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
use LogicException;
use Opulence\Api\ResponseFactories\BadRequestResponseFactory;
use Opulence\Api\ResponseFactories\ConflictResponseFactory;
use Opulence\Api\ResponseFactories\CreatedResponseFactory;
use Opulence\Api\ResponseFactories\ForbiddenResponseFactory;
use Opulence\Api\ResponseFactories\FoundResponseFactory;
use Opulence\Api\ResponseFactories\InternalServerErrorResponseFactory;
use Opulence\Api\ResponseFactories\MovedPermanentlyResponseFactory;
use Opulence\Api\ResponseFactories\NoContentResponseFactory;
use Opulence\Api\ResponseFactories\NotFoundResponseFactory;
use Opulence\Api\ResponseFactories\OkResponseFactory;
use Opulence\Api\ResponseFactories\UnauthorizedResponseFactory;
use Opulence\Net\Http\Formatting\RequestParser;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Uri;
use Opulence\Serialization\SerializationException;

/**
 * Defines the base class for controllers to extend
 */
class Controller
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
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request context is not set
     */
    protected function badRequest($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->requestContext instanceof RequestContext) {
            throw new LogicException('Request context is not set');
        }

        return (new BadRequestResponseFactory($headers, $body))->createResponse($this->requestContext);
    }

    /**
     * Creates a conflict response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request context is not set
     */
    protected function conflict($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->requestContext instanceof RequestContext) {
            throw new LogicException('Request context is not set');
        }

        return (new ConflictResponseFactory($headers, $body))->createResponse($this->requestContext);
    }

    /**
     * Creates a created response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request context is not set
     */
    protected function created($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->requestContext instanceof RequestContext) {
            throw new LogicException('Request context is not set');
        }

        return (new CreatedResponseFactory($headers, $body))->createResponse($this->requestContext);
    }

    /**
     * Creates a forbidden response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request context is not set
     */
    protected function forbidden($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->requestContext instanceof RequestContext) {
            throw new LogicException('Request context is not set');
        }

        return (new ForbiddenResponseFactory($headers, $body))->createResponse($this->requestContext);
    }

    /**
     * Creates a found redirect response
     *
     * @param string|Uri $uri The URI to redirect to
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws InvalidArgumentException Thrown if the URI is not a string nor a URI
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request context is not set
     */
    protected function found($uri, $body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->requestContext instanceof RequestContext) {
            throw new LogicException('Request context is not set');
        }

        return (new FoundResponseFactory($uri, $headers, $body))->createResponse($this->requestContext);
    }

    /**
     * Creates an internal server error response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request context is not set
     */
    protected function internalServerError($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->requestContext instanceof RequestContext) {
            throw new LogicException('Request context is not set');
        }

        return (new InternalServerErrorResponseFactory($headers, $body))->createResponse($this->requestContext);
    }

    /**
     * Creates a moved permanently redirect response
     *
     * @param string|Uri $uri The URI to redirect to
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws InvalidArgumentException Thrown if the URI is not a string nor a URI
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request context is not set
     */
    protected function movedPermanently($uri, $body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->requestContext instanceof RequestContext) {
            throw new LogicException('Request context is not set');
        }

        return (new MovedPermanentlyResponseFactory($uri, $headers, $body))->createResponse($this->requestContext);
    }

    /**
     * Creates a no content response
     *
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request context is not set
     */
    protected function noContent(HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->requestContext instanceof RequestContext) {
            throw new LogicException('Request context is not set');
        }

        return (new NoContentResponseFactory($headers))->createResponse($this->requestContext);
    }

    /**
     * Creates a not found response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request context is not set
     */
    protected function notFound($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->requestContext instanceof RequestContext) {
            throw new LogicException('Request context is not set');
        }

        return (new NotFoundResponseFactory($headers, $body))->createResponse($this->requestContext);
    }

    /**
     * Creates an OK response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request context is not set
     */
    protected function ok($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->requestContext instanceof RequestContext) {
            throw new LogicException('Request context is not set');
        }

        return (new OkResponseFactory($headers, $body))->createResponse($this->requestContext);
    }

    /**
     * Reads the request body as an array of a type
     *
     * @param string $type The type to read as an array of
     * @return array The body as an array of the input type
     * @throws HttpException Thrown if there was an error reading the body
     */
    protected function readRequestBodyAsArrayOfType(string $type): array
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
     * Creates an unauthorized response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request context is not set
     */
    protected function unauthorized($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->requestContext instanceof RequestContext) {
            throw new LogicException('Request context is not set');
        }

        return (new UnauthorizedResponseFactory($headers, $body))->createResponse($this->requestContext);
    }
}
