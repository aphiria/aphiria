<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api;

use Opulence\Net\Http\Formatting\RequestParser;

/**
 * Defines the base class for controllers to extend
 */
abstract class Controller
{
    /** @var RequestContext The current request context */
    protected $requestContext;
    /** @var RequestParser The parser to use to get data from the current request */
    protected $requestParser;

    /**
     * Sets the current request context
     *
     * @param RequestContext $requestContext The current request context
     * @internal
     */
    public function setRequestContext(RequestContext $requestContext): void
    {
        $this->requestContext = $requestContext;
    }

    /**
     * Sets the request parser
     *
     * @param RequestParser $requestParser The request parser
     * @internal
     */
    public function setRequestParser(RequestParser $requestParser): void
    {
        $this->requestParser = $requestParser;
    }

    /**
     * Creates a bad request response
     *
     * @param \object|scalar|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     */
    protected function badRequest($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_BAD_REQUEST, $headers, $body);
    }

    /**
     * Creates a conflict response
     *
     * @param \object|scalar|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     */
    protected function conflict($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_CONFLICT, $headers, $body);
    }

    /**
     * Creates a created response
     *
     * @param \object|scalar|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     */
    protected function created($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_CREATED, $headers, $body);
    }

    /**
     * Creates a response
     *
     * @param int $statusCode The HTTP status code
     * @param HttpHeaders|null $headers The headers to use in the response
     * @param \object|scalar|array|null $body The raw response body
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if response content negotiation failed or if the body could not be serialized
     * @internal
     */
    protected function createResponse(int $statusCode, ?HttpHeaders $headers, $body): IHttpResponseMessage
    {
        $headers = $headers ?? new HttpHeaders();

        if ($this->requestContext->getResponseContentNegotiationResult() === null) {
            throw new HttpException(HttpStatusCodes::HTTP_NOT_ACCEPTABLE, 'Response content could not be negotiated');
        }

        $mediaType = $this->requestContext->getResponseContentNegotiationResult()->getMediaType();

        if ($mediaType !== null) {
            $headers->add('Content-Type', $mediaType);
        }

        try {
            return new Response(
                $statusCode,
                $headers,
                $this->createResponseBody($body)
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
     * @return IHttpBody|null The response body, or null if there is no body
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws HttpException Thrown if the response content could not be negotiated
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

        if ($this->requestContext->getResponseContentNegotiationResult() === null) {
            throw new HttpException(HttpStatusCodes::HTTP_NOT_ACCEPTABLE, 'Response content could not be negotiated');
        }

        $bodyStream = new Stream(fopen('php://temp', 'r+b'));

        try {
            $this->requestContext->getResponseContentNegotiationResult()->getFormatter()->writeToStream($body, $bodyStream);
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

    /**
     * Creates a forbidden response
     *
     * @param \object|scalar|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     */
    protected function forbidden($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_FORBIDDEN, $headers, $body);
    }

    /**
     * Creates an internal server error response
     *
     * @param \object|scalar|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     */
    protected function internalServerError($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR, $headers, $body);
    }

    /**
     * Creates a no content response
     *
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     */
    protected function noContent(HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_NO_CONTENT, $headers, null);
    }

    /**
     * Creates a not found response
     *
     * @param \object|scalar|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     */
    protected function notFound($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_NOT_FOUND, $headers, $body);
    }

    /**
     * Creates an OK response
     *
     * @param \object|scalar|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     */
    protected function ok($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_OK, $headers, $body);
    }

    /**
     * Reads the request body as an array of a type
     *
     * @param string $type The type to read as an array of
     * @return array The body as an array of the input type
     * @throws HttpException Thrown if there was an error reading the body
     */
    protected function readBodyAsArrayOf(string $type): array
    {
        if ($this->requestContext->getRequestContentNegotiationResult() === null) {
            throw new HttpException(HttpStatusCodes::HTTP_UNSUPPORTED_MEDIA_TYPE, 'Failed to read request body');
        }

        if (($body = $this->requestContext->getRequest()->getBody()) === null) {
            return [];
        }

        try {
            return $this->requestContext->getRequestContentNegotiationResult()->getFormatter()
                ->readFromStream($body->readAsStream(), $type, true);
        } catch (SerializationException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                'Failed to deserialize request body',
                0,
                $ex
            );
        }
    }

    /**
     * Creates a redirect response
     *
     * @param \object|scalar|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     */
    protected function redirect($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_MOVED_PERMANENTLY, $headers, $body);
    }

    /**
     * Creates an unauthorized response
     *
     * @param \object|scalar|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     */
    protected function unauthorized($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        return $this->createResponse(HttpStatusCodes::HTTP_UNAUTHORIZED, $headers, $body);
    }
}
