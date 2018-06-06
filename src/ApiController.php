<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use Opulence\IO\Streams\Stream;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Response;
use Opulence\Net\Http\StreamBody;
use Opulence\Net\Http\StringBody;

/**
 * Defines the base class for API controllers to extend
 */
abstract class ApiController extends Controller
{
    /**
     * Creates a bad request response
     *
     * @param IHttpBody|IStream|scalar|array|\object|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     */
    protected function badRequest($body, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_BAD_REQUEST, $headers, $body);
    }

    /**
     * Creates a conflict response
     *
     * @param IHttpBody|IStream|scalar|array|\object|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     */
    protected function conflict($body, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_CONFLICT, $headers, $body);
    }

    /**
     * Creates a created response
     *
     * @param IHttpBody|IStream|scalar|array|\object|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     */
    protected function created($body, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_CREATED, $headers, $body);
    }

    /**
     * Creates a response
     *
     * @param int $statusCode The HTTP status code
     * @param HttpHeaders|null $headers The headers to use in the response
     * @param IHttpBody|IStream|scalar|array|\object|null $body The raw response body
     * @return IHttpResponseMessage The response
     * @throws FailedContentNegotiationException Thrown if content negotiation failed
     * @internal
     */
    protected function createResponse(int $statusCode, ?HttpHeaders $headers, $body): IHttpResponseMessage
    {
        $headers = $headers ?? new HttpHeaders();

        if ($this->context->getResponseContentNegotiationResult() === null) {
            throw new FailedContentNegotiationException('Response content could not be negotiated');
        }

        $mediaType = $this->context->getResponseContentNegotiationResult()->getMediaType();

        if ($mediaType !== null) {
            $headers->add('Content-Type', $mediaType);
        }

        return new Response(
            $statusCode,
            $headers,
            $this->createResponseBody($body)
        );
    }

    /**
     * Creates a response body from a raw body value
     *
     * @param IHttpBody|IStream|scalar|array|\object $body The raw response body
     * @return IHttpBody|null The response body, or null if there is no body
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws FailedContentNegotiationException Thrown if the response content could not be negotiated
     */
    protected function createResponseBody($body): ?IHttpBody
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

        if ($this->context->getResponseContentNegotiationResult() === null) {
            throw new FailedContentNegotiationException('Response content could not be negotiated');
        }

        $bodyStream = new Stream(fopen('php://temp', 'r+b'));
        $this->context->getResponseContentNegotiationResult()->getFormatter()->writeToStream($body, $bodyStream);

        return new StreamBody($bodyStream);
    }

    /**
     * Creates a forbidden response
     *
     * @param IHttpBody|IStream|scalar|array|\object|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     */
    protected function forbidden($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_FORBIDDEN, $headers, $body);
    }

    /**
     * Creates an internal server error response
     *
     * @param IHttpBody|IStream|scalar|array|\object|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     */
    protected function internalServerError($body, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $headers, $body);
    }

    /**
     * Creates a no content response
     *
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     */
    protected function noContent(HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_NO_CONTENT, $headers, null);
    }

    /**
     * Creates a not found response
     *
     * @param IHttpBody|IStream|scalar|array|\object|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     */
    protected function notFound($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_NOT_FOUND, $headers, $body);
    }

    /**
     * Creates an OK response
     *
     * @param IHttpBody|IStream|scalar|array|\object|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     */
    protected function ok($body, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_OK, $headers, $body);
    }

    /**
     * Reads the request body as an array of a type
     *
     * @param string $type The type to read as an array of
     * @return array The body as an array of the input type
     * @throws FailedContentNegotiationException Thrown if the request content could not be negotiated
     */
    protected function readBodyAsArrayOf(string $type): array
    {
        if ($this->context->getRequestContentNegotiationResult() === null) {
            throw new FailedContentNegotiationException('Request content could not be negotiated');
        }

        if (($body = $this->context->getRequest()->getBody()) === null) {
            return [];
        }

        return $this->context->getRequestContentNegotiationResult()->getFormatter()
            ->readFromStream($type, $body->readAsStream(), true);
    }

    /**
     * Creates a redirect response
     *
     * @param IHttpBody|IStream|scalar|array|\object|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     */
    protected function redirect($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_MOVED_PERMANENTLY, $headers, $body);
    }

    /**
     * Creates an unauthorized response
     *
     * @param IHttpBody|IStream|scalar|array|\object|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     */
    protected function unauthorized($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_UNAUTHORIZED, $headers, $body);
    }
}
