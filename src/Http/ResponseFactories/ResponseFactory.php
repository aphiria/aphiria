<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\ResponseFactories;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use Opulence\IO\Streams\Stream;
use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\RequestContext;
use Opulence\Net\Http\Response;
use Opulence\Net\Http\StreamBody;
use Opulence\Net\Http\StringBody;
use Opulence\Serialization\SerializationException;

/**
 * Defines the base response factory
 */
class ResponseFactory implements IResponseFactory
{
    /** @var int The HTTP status code */
    protected $statusCode;
    /** @var HttpHeaders The HTTP headers */
    protected $headers;
    /** @var \object|string|int|float|array|null $body The raw response body The raw body */
    protected $rawBody;

    /**
     * @param int $statusCode The HTTP status code
     * @param HttpHeaders|null $headers The headers of the response
     * @param \object|string|int|float|array|null $rawBody The raw body to use in the response
     */
    public function __construct(int $statusCode, HttpHeaders $headers = null, $rawBody = null)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers ?? new HttpHeaders();
        $this->rawBody = $rawBody;
    }

    /**
     * @inheritdoc
     */
    public function createResponse(RequestContext $requestContext): IHttpResponseMessage
    {
        $responseContentNegotiationResult = $requestContext->getResponseContentNegotiationResult();
        $mediaType = $responseContentNegotiationResult->getMediaType();

        if ($mediaType !== null) {
            $this->headers->add('Content-Type', $mediaType);
        }

        try {
            return new Response(
                $this->statusCode,
                $this->headers,
                $this->createResponseBody($responseContentNegotiationResult)
            );
        } catch (InvalidArgumentException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                'Failed to create response body',
                0,
                $ex
            );
        }
    }

    /**
     * Creates a response body from a raw body value
     *
     * @param ContentNegotiationResult $responseContentNegotiationResult The response content negotiation result
     * @return IHttpBody|null The response body, or null if there is no body
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws HttpException Thrown if the response content could not be negotiated
     */
    protected function createResponseBody(ContentNegotiationResult $responseContentNegotiationResult): ?IHttpBody
    {
        if ($this->rawBody === null || $this->rawBody instanceof IHttpBody) {
            return $this->rawBody;
        }

        if ($this->rawBody instanceof IStream) {
            $this->addContentLengthHeader($this->rawBody->getLength());

            return new StreamBody($this->rawBody);
        }

        if (\is_scalar($this->rawBody)) {
            $stringBody = (string)$this->rawBody;
            $this->addContentLengthHeader(\mb_strlen($stringBody, '8bit'));

            return new StringBody($stringBody);
        }

        if ((!\is_object($this->rawBody) && !\is_array($this->rawBody)) || \is_callable($this->rawBody)) {
            throw new InvalidArgumentException('Unsupported body type ' . \gettype($this->rawBody));
        }

        $mediaTypeFormatter = $responseContentNegotiationResult->getFormatter();

        if ($mediaTypeFormatter === null) {
            throw new HttpException(HttpStatusCodes::HTTP_NOT_ACCEPTABLE, 'Response content could not be negotiated');
        }

        $bodyStream = new Stream(fopen('php://temp', 'r+b'));

        try {
            $mediaTypeFormatter->writeToStream(
                $this->rawBody,
                $bodyStream,
                $responseContentNegotiationResult->getEncoding()
            );
        } catch (SerializationException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                'Failed to serialize response body',
                0,
                $ex
            );
        }

        $this->addContentLengthHeader($bodyStream->getLength());

        return new StreamBody($bodyStream);
    }

    /**
     * Adds the Content-Length header if it does not already exist
     *
     * @param int|null $contentLength The content length, or null if it isn't known
     */
    private function addContentLengthHeader(?int $contentLength): void
    {
        // Don't bother if the Content-Length header is already set
        if ($contentLength === null || $this->headers->containsKey('Content-Length')) {
            return;
        }

        $this->headers->add('Content-Length', $contentLength);
    }
}
