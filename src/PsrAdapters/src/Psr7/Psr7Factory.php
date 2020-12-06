<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\PsrAdapters\Psr7;

use Aphiria\Collections\KeyValuePair;
use Aphiria\IO\Streams\IStream;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\Formatting\RequestHeaderParser;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\MultipartBody;
use Aphiria\Net\Http\MultipartBodyPart;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StreamBody;
use Aphiria\Net\Uri;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Defines the class that can create PSR-7 objects from Aphiria objects
 */
class Psr7Factory implements IPsr7Factory
{
    /** @var RequestHeaderParser The Aphiria request header parser */
    private RequestHeaderParser $aphiriaRequestHeaderParser;
    /** @var RequestParser The Aphiria request parser */
    private RequestParser $aphiriaRequestParser;

    /**
     * @param ServerRequestFactoryInterface $psr7RequestFactory The PSR-7 request factory
     * @param ResponseFactoryInterface $psr7ResponseFactory The PSR-7 response factory
     * @param StreamFactoryInterface $psr7StreamFactory The PSR-7 stream factory
     * @param UploadedFileFactoryInterface $psr7UploadedFileFactory The PSR-7 uploaded file factory
     * @param UriFactoryInterface $psr7UriFactory The PSR-7 URI factory
     * @param RequestHeaderParser|null $aphiriaRequestHeaderParser The Aphiria request header parser
     * @param RequestParser|null $aphiriaRequestParser The Aphiria request parser
     */
    public function __construct(
        private ServerRequestFactoryInterface $psr7RequestFactory,
        private ResponseFactoryInterface $psr7ResponseFactory,
        private StreamFactoryInterface $psr7StreamFactory,
        private UploadedFileFactoryInterface $psr7UploadedFileFactory,
        private UriFactoryInterface $psr7UriFactory,
        RequestHeaderParser $aphiriaRequestHeaderParser = null,
        RequestParser $aphiriaRequestParser = null
    ) {
        $this->aphiriaRequestHeaderParser = $aphiriaRequestHeaderParser ?? new RequestHeaderParser();
        $this->aphiriaRequestParser = $aphiriaRequestParser ?? new RequestParser($this->aphiriaRequestHeaderParser);
    }

    /**
     * @inheritdoc
     */
    public function createAphiriaRequest(ServerRequestInterface $psr7Request): IRequest
    {
        $aphiriaRequest = new Request(
            $psr7Request->getMethod(),
            $this->createAphiriaUri($psr7Request->getUri()),
            protocolVersion: $psr7Request->getProtocolVersion()
        );

        foreach ($psr7Request->getHeaders() as $name => $values) {
            $aphiriaRequest->getHeaders()->add($name, $values);
        }

        if ($this->aphiriaRequestParser->isMultipart($aphiriaRequest)) {
            $bodyParts = [];

            /** @var UploadedFileInterface $uploadedFile */
            foreach ($psr7Request->getUploadedFiles() as $name => $uploadedFile) {
                $bodyPart = new MultipartBodyPart(
                    new Headers(),
                    new StreamBody($this->createAphiriaStream($uploadedFile->getStream()))
                );
                $contentDisposition = "name=$name";

                if (($psr7ClientFilename = $uploadedFile->getClientFilename()) !== null) {
                    $contentDisposition .= "; filename=$psr7ClientFilename";
                }

                $bodyPart->getHeaders()->add('Content-Disposition', $contentDisposition);

                if (($psr7ClientMimeType = $uploadedFile->getClientMediaType()) !== null) {
                    $bodyPart->getHeaders()->add('Content-Type', $psr7ClientMimeType);
                }

                $bodyParts[] = $bodyPart;
            }

            $boundary = null;
            $this->aphiriaRequestParser->parseParameters($aphiriaRequest, 'Content-Type')->tryGet('boundary', $boundary);
            /** @var string|null $boundary */
            $aphiriaRequest->setBody(new MultipartBody($bodyParts, $boundary));
        } else {
            $aphiriaRequest->setBody(new StreamBody($this->createAphiriaStream($psr7Request->getBody())));
        }

        if (($psr7ParsedBody = $psr7Request->getParsedBody()) !== null) {
            $aphiriaRequest->getProperties()->add('__APHIRIA_PARSED_BODY', $psr7ParsedBody);
        }

        /** @psalm-suppress MixedAssignment The values could legitimately be mixed */
        foreach ($psr7Request->getAttributes() as $name => $value) {
            $aphiriaRequest->getProperties()->add($name, $value);
        }

        return $aphiriaRequest;
    }

    /**
     * @inheritdoc
     */
    public function createAphiriaResponse(ResponseInterface $psr7Response): IResponse
    {
        $aphiriaResponse = new Response(
            $psr7Response->getStatusCode(),
            null,
            new StreamBody($this->createAphiriaStream($psr7Response->getBody())),
            $psr7Response->getProtocolVersion()
        );

        foreach ($psr7Response->getHeaders() as $name => $values) {
            $aphiriaResponse->getHeaders()->add($name, $values);
        }

        return $aphiriaResponse;
    }

    /**
     * @inheritdoc
     */
    public function createAphiriaStream(StreamInterface $psr7Stream): IStream
    {
        $aphiriaStream = new Stream(fopen('php://temp', 'r+b'));
        $psr7Stream->rewind();

        while (!$psr7Stream->eof()) {
            $aphiriaStream->write($psr7Stream->read(8192));
        }

        return $aphiriaStream;
    }

    /**
     * @inheritdoc
     */
    public function createAphiriaUri(UriInterface $psr7Uri): Uri
    {
        return new Uri((string)$psr7Uri);
    }

    /**
     * @inheritdoc
     */
    public function createPsr7Request(IRequest $aphiriaRequest): ServerRequestInterface
    {
        $psr7Request = $this->psr7RequestFactory->createServerRequest(
            $aphiriaRequest->getMethod(),
            (string)$aphiriaRequest->getUri()
        );

        /** @var KeyValuePair $kvp */
        foreach ($aphiriaRequest->getHeaders() as $kvp) {
            /** @var string|string[] $headerValue */
            foreach ($kvp->getValue() as $headerValue) {
                $psr7Request = $psr7Request->withHeader((string)$kvp->getKey(), $headerValue);
            }
        }

        if (($aphiriaBody = $aphiriaRequest->getBody()) !== null) {
            $psr7Request = $psr7Request->withBody($this->createPsr7Stream($aphiriaBody->readAsStream()));
        }

        $psr7Request = $psr7Request->withUploadedFiles($this->createPsr7UploadedFiles($aphiriaRequest));
        $psr7CookieParams = $psr7QueryParams = [];

        /** @var KeyValuePair $kvp */
        foreach ($this->aphiriaRequestParser->parseCookies($aphiriaRequest) as $kvp) {
            /**
             * @psalm-suppress MixedArrayOffset Purposely building a mixed array
             * @psalm-suppress MixedAssignment Ditto
             */
            $psr7CookieParams[$kvp->getKey()] = $kvp->getValue();
        }

        /** @var KeyValuePair $kvp */
        foreach ($this->aphiriaRequestParser->parseQueryString($aphiriaRequest) as $kvp) {
            /**
             * @psalm-suppress MixedArrayOffset Purposely building a mixed array
             * @psalm-suppress MixedAssignment Ditto
             */
            $psr7QueryParams[$kvp->getKey()] = $kvp->getValue();
        }

        $psr7Request = $psr7Request->withCookieParams($psr7CookieParams)
            ->withQueryParams($psr7QueryParams);

        $parsedBody = null;

        if ($aphiriaRequest->getProperties()->tryGet('__APHIRIA_PARSED_BODY', $parsedBody)) {
            /** @var array|object|null $parsedBody */
            $psr7Request = $psr7Request->withParsedBody($parsedBody);
        }

        /** @var KeyValuePair $kvp */
        foreach ($aphiriaRequest->getProperties() as $kvp) {
            $psr7Request = $psr7Request->withAttribute((string)$kvp->getKey(), $kvp->getValue());
        }

        return $psr7Request;
    }

    /**
     * @inheritdoc
     */
    public function createPsr7Response(IResponse $aphiriaResponse): ResponseInterface
    {
        $psr7Response = $this->psr7ResponseFactory->createResponse(
            $aphiriaResponse->getStatusCode(),
            $aphiriaResponse->getReasonPhrase() ?? ''
        )
            ->withProtocolVersion($aphiriaResponse->getProtocolVersion());

        /** @var KeyValuePair $kvp */
        foreach ($aphiriaResponse->getHeaders() as $kvp) {
            /** @var string|string[] $headerValue */
            foreach ((array)$kvp->getValue() as $headerValue) {
                $psr7Response = $psr7Response->withHeader((string)$kvp->getKey(), $headerValue);
            }
        }

        if (($aphiriaBody = $aphiriaResponse->getBody()) !== null) {
            $psr7Response = $psr7Response->withBody($this->createPsr7Stream($aphiriaBody->readAsStream()));
        }

        return $psr7Response;
    }

    /**
     * @inheritdoc
     */
    public function createPsr7Stream(IStream $stream): StreamInterface
    {
        $stream->rewind();
        $handle = \fopen('php://temp', 'r+b');

        while (!$stream->isEof()) {
            \fwrite($handle, $stream->read(8192));
        }

        return $this->psr7StreamFactory->createStreamFromResource($handle);
    }

    /**
     * @inheritdoc
     */
    public function createPsr7UploadedFiles(IRequest $aphiriaRequest): array
    {
        if (
            !$this->aphiriaRequestParser->isMultipart($aphiriaRequest)
            || ($multipartBody = $this->aphiriaRequestParser->readAsMultipart($aphiriaRequest)) === null
        ) {
            return [];
        }

        $psr7UploadedFiles = [];

        foreach ($multipartBody->getParts() as $i => $part) {
            if (($partBody = $part->getBody()) === null) {
                continue;
            }

            $contentDispositionParameters = $this->aphiriaRequestHeaderParser->parseParameters(
                $part->getHeaders(),
                'Content-Disposition'
            );
            $name = $filename = null;

            if (!$contentDispositionParameters->tryGet('name', $name)) {
                $name = (string)$i;
            }

            /** @var string $name */
            $contentDispositionParameters->tryGet('filename', $filename);
            /** @var string|null $filename */
            $psr7UploadedFiles[$name] = $this->psr7UploadedFileFactory->createUploadedFile(
                $this->createPsr7Stream($partBody->readAsStream()),
                $partBody->getLength(),
                \UPLOAD_ERR_OK,
                $filename,
                $this->aphiriaRequestParser->getClientMimeType($part)
            );
        }

        return $psr7UploadedFiles;
    }

    /**
     * @inheritdoc
     */
    public function createPsr7Uri(Uri $aphiriaUri): UriInterface
    {
        return $this->psr7UriFactory->createUri((string)$aphiriaUri);
    }
}
