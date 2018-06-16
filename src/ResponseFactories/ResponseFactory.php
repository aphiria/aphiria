<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\ResponseFactories;

use InvalidArgumentException;
use Opulence\Api\RequestContext;
use Opulence\IO\IStream;
use Opulence\IO\Stream;
use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Response;
use Opulence\Net\Http\StreamBody;
use Opulence\Net\Http\StringBody;
use Opulence\Serialization\SerializationException;

/**
 * Defines the base response factory
 */
abstract class ResponseFactory implements IResponseFactory
{
    /** @var int The HTTP status code */
    protected $statusCode;
    /** @var HttpHeaders The HTTP headers */
    protected $headers;
    /** @var \object|string|int|float|array|null $body The raw response body The raw body */
    protected $rawBody;

    /**
     * @param int $statusCode The HTTP status code
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
        if ($requestContext->getResponseContentNegotiationResult() === null) {
            throw new HttpException(HttpStatusCodes::HTTP_NOT_ACCEPTABLE, 'Response content could not be negotiated');
        }

        $mediaType = $requestContext->getResponseContentNegotiationResult()->getMediaType();

        if ($mediaType !== null) {
            $this->headers->add('Content-Type', $mediaType);
        }

        try {
            return new Response(
                $this->statusCode,
                $this->headers,
                $this->createResponseBody($this->rawBody, $requestContext->getRequestContentNegotiationResult())
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
     * @param IHttpBody|IStream|int|float|string|array|\object $body The raw response body
     * @param ContentNegotiationResult|null $requestContentNegotiationResult The request content negotiation result
     * @return IHttpBody|null The response body, or null if there is no body
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws HttpException Thrown if the response content could not be negotiated
     */
    protected function createResponseBody($body, ?ContentNegotiationResult $requestContentNegotiationResult): ?IHttpBody
    {
        if ($body === null || $body instanceof IHttpBody) {
            return $body;
        }

        if ($body instanceof IStream) {
            return new StreamBody($body);
        }

        if (\is_scalar($body)) {
            return new StringBody((string)$body);
        }

        if (!\is_object($body) && !\is_array($body)) {
            throw new InvalidArgumentException('Unsupported body type ' . \gettype($body));
        }

        if ($requestContentNegotiationResult === null) {
            throw new HttpException(HttpStatusCodes::HTTP_NOT_ACCEPTABLE, 'Response content could not be negotiated');
        }

        $bodyStream = new Stream(fopen('php://temp', 'r+b'));

        try {
            $requestContentNegotiationResult->getFormatter()->writeToStream(
                $body,
                $bodyStream
            );
        } catch (SerializationException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                'Failed to serialize response body',
                0,
                $ex
            );
        }

        return new StreamBody($bodyStream);
    }
}
