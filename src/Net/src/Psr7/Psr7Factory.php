<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Psr7;

use Aphiria\Net\Http\Formatting\RequestHeaderParser;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;

/**
 * Defines the class that can create PSR-7 objects from Aphiria objects
 */
class Psr7Factory
{
    /** @var ServerRequestFactoryInterface The PSR-7 request factory */
    private ServerRequestFactoryInterface $psr7RequestFactory;
    /** @var ResponseFactoryInterface The PSR-7 response factory */
    private ResponseFactoryInterface $psr7ResponseFactory;
    /** @var StreamFactoryInterface The PSR-7 stream factory */
    private StreamFactoryInterface $psr7StreamFactory;
    /** @var UploadedFileFactoryInterface The PSR-7 uploaded file factory */
    private UploadedFileFactoryInterface $psr7UploadedFactoryInterface;
    /** @var RequestHeaderParser The Aphiria request header parser */
    private RequestHeaderParser $aphiriaRequestHeaderParser;
    /** @var RequestParser The Aphiria request parser */
    private RequestParser $aphiriaRequestParser;

    /**
     * @param ServerRequestFactoryInterface $psr7RequestFactory The PSR-7 request factory
     * @param ResponseFactoryInterface $psr7ResponseFactory The PSR-7 response factory
     * @param StreamFactoryInterface $psr7StreamFactory The PSR-7 stream factory
     * @param UploadedFileFactoryInterface $psr7UploadedFactoryInterface The PSR-7 uploaded file factory
     */
    public function __construct(
        ServerRequestFactoryInterface $psr7RequestFactory,
        ResponseFactoryInterface $psr7ResponseFactory,
        StreamFactoryInterface $psr7StreamFactory,
        UploadedFileFactoryInterface $psr7UploadedFactoryInterface
    ) {
        $this->psr7RequestFactory = $psr7RequestFactory;
        $this->psr7ResponseFactory = $psr7ResponseFactory;
        $this->psr7StreamFactory = $psr7StreamFactory;
        $this->psr7UploadedFactoryInterface = $psr7UploadedFactoryInterface;
        $this->aphiriaRequestHeaderParser = new RequestHeaderParser();
        $this->aphiriaRequestParser = new RequestParser($this->aphiriaRequestHeaderParser);
    }

    /**
     * Creates a PSR-7 request from an Aphiria request
     *
     * @param IHttpRequestMessage $aphiriaRequest The Aphiria request to create a PSR-7 request from
     * @return ServerRequestInterface The PSR-7 request
     */
    public function createPsr7Request(IHttpRequestMessage $aphiriaRequest): ServerRequestInterface
    {
        $psr7Request = $this->psr7RequestFactory->createServerRequest(
            $aphiriaRequest->getMethod(),
            (string)$aphiriaRequest->getUri(),
            // TODO: How do I get the original SERVER vars?
        );

        foreach ($aphiriaRequest->getHeaders() as $kvp) {
            $psr7Request = $psr7Request->withHeader($kvp->getKey(), $kvp->getValue());
        }

        if (($aphiriaBody = $aphiriaRequest->getBody()) !== null) {
            $psr7Body = $this->psr7StreamFactory->createStreamFromResource($aphiriaBody->readAsStream()->readAsResource());
            $psr7Request = $psr7Request->withBody($psr7Body);
        }

        if (($multipartBody = $this->aphiriaRequestParser->readAsMultipart($aphiriaRequest)) !== null) {
            $psr7UploadedFiles = [];

            foreach ($multipartBody->getParts() as $i => $part) {
                if (($partBody = $part->getBody()) === null) {
                    continue;
                }

                $parsedContentDisposition = $this->aphiriaRequestHeaderParser->parseParameters(
                    $part->getHeaders(),
                    'Content-Disposition'
                );
                $filename = null;

                if (!$parsedContentDisposition->tryGet('filename', $filename)) {
                    $filename = (string)$i;
                }

                $psr7UploadedFiles[$filename] = $this->psr7UploadedFactoryInterface->createUploadedFile(
                    $this->psr7StreamFactory->createStreamFromResource($partBody->readAsStream()->readAsResource()),
                    $partBody->getLength()
                );
            }

            $psr7Request = $psr7Request->withUploadedFiles($psr7UploadedFiles);
        }


        $psr7CookieParams = $this->aphiriaRequestParser->parseCookies($aphiriaRequest)->toArray();
        $psr7QueryParams = $this->aphiriaRequestParser->parseQueryString($aphiriaRequest)->toArray();
        $psr7Request = $psr7Request->withCookieParams($psr7CookieParams)
            ->withQueryParams($psr7QueryParams);

        // TODO: How can I grab the parsed body from an Aphiria request?

        foreach ($aphiriaRequest->getProperties() as $kvp) {
            $psr7Request = $psr7Request->withAttribute($kvp->getKey(), $kvp->getValue());
        }

        return $psr7Request;
    }

    /**
     * Creates a PSR-7 request from an Aphiria response
     *
     * @param IHttpResponseMessage $aphiriaResponse The Aphiria response to create a PSR-7 response from
     * @return ResponseInterface The PSR-7 response
     */
    public function createPsr7Response(IHttpResponseMessage $aphiriaResponse): ResponseInterface
    {
        $psr7Response = $this->psr7ResponseFactory->createResponse(
            $aphiriaResponse->getStatusCode(),
            $aphiriaResponse->getReasonPhrase()
        )
            ->withProtocolVersion($aphiriaResponse->getProtocolVersion());

        foreach ($aphiriaResponse->getHeaders() as $kvp) {
            $psr7Response = $psr7Response->withHeader($kvp->getKey(), $kvp->getValue());
        }

        if (($aphiriaBody = $aphiriaResponse->getBody()) !== null) {
            $psr7Body = $this->psr7StreamFactory->createStreamFromResource($aphiriaBody->readAsStream()->readAsResource());
            $psr7Response = $psr7Response->withBody($psr7Body);
        }

        return $psr7Response;
    }
}
