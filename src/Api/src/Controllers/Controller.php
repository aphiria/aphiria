<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Controllers;

use Aphiria\Authentication\IUserAccessor;
use Aphiria\ContentNegotiation\FailedContentNegotiationException;
use Aphiria\ContentNegotiation\IBodyDeserializer;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\Formatting\ResponseFormatter;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Uri;
use Aphiria\Security\IPrincipal;
use InvalidArgumentException;
use LogicException;

/**
 * Defines the base class for controllers to extend
 */
class Controller
{
    /**
     * The body deserializer
     *
     * @var IBodyDeserializer
     * @throw LogicException Thrown if the virtual body deserializer is not set
     */
    public IBodyDeserializer $bodyDeserializer {
        get => $this->_bodyDeserializer === null ? throw new LogicException('Body deserializer is not set') : $this->_bodyDeserializer;
        set => $this->_bodyDeserializer = $value;
    }
    /**
     * The current request
     *
     * @var IRequest
     * @throw LogicException Thrown if the virtual request is not set
     */
    public IRequest $request {
        get => $this->_request === null ? throw new LogicException('Request is not set') : $this->_request;
        set => $this->_request = $value;
    }
    /**
     * The request parser
     *
     * @var RequestParser
     * @throw LogicException Thrown if the virtual request parser is not set
     */
    public RequestParser $requestParser {
        get => $this->_requestParser === null ? throw new LogicException('Request parser is not set') : $this->_requestParser;
        set => $this->_requestParser = $value;
    }
    /**
     * The response factory
     *
     * @var IResponseFactory
     * @throw LogicException Thrown if the virtual response factory is not set
     */
    public IResponseFactory $responseFactory {
        get => $this->_responseFactory === null ? throw new LogicException('Response factory is not set') : $this->_responseFactory;
        set => $this->_responseFactory = $value;
    }
    /**
     * The response formatter
     *
     * @var ResponseFormatter
     * @throw LogicException Thrown if the virtual response formatter is not set
     */
    public ResponseFormatter $responseFormatter {
        get => $this->_responseFormatter === null ? throw new LogicException('Response formatter is not set') : $this->_responseFormatter;
        set => $this->_responseFormatter = $value;
    }
    /**
     * The user accessor
     *
     * @var IUserAccessor
     * @throw LogicException Thrown if the virtual user accessor is not set
     */
    public IUserAccessor $userAccessor {
        get => $this->_userAccessor === null ? throw new LogicException('User accessor is not set') : $this->_userAccessor;
        set => $this->_userAccessor = $value;
    }
    /**
     * Gets the current user if one was set, otherwise null
     *
     * @var IPrincipal|null
     * @throws LogicException Thrown if the user accessor or request are not set
     */
    protected ?IPrincipal $user {
        get {
            return $this->userAccessor->getUser($this->request);
        }
    }
    /** @var IBodyDeserializer|null The virtual body deserializer */
    private ?IBodyDeserializer $_bodyDeserializer = null;
    /** @var IRequest|null The virtual current request */
    private ?IRequest $_request = null;
    /** @var RequestParser|null The virtual parser to use to get data from the current request */
    private ?RequestParser $_requestParser = null;
    /** @var IResponseFactory|null The response factory */
    private ?IResponseFactory $_responseFactory = null;
    /** @var ResponseFormatter|null The virtual formatter to use to write data to the response */
    private ?ResponseFormatter $_responseFormatter = null;
    /** @var IUserAccessor|null The virtual user accessor */
    private ?IUserAccessor $_userAccessor = null;

    /**
     * Creates an accepted response
     *
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function accepted(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
    {
        return $this->responseFactory->createResponse(
            $this->request,
            HttpStatusCode::Accepted,
            $headers,
            $body
        );
    }

    /**
     * Creates a bad request response
     *
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function badRequest(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
    {
        return $this->responseFactory->createResponse(
            $this->request,
            HttpStatusCode::BadRequest,
            $headers,
            $body
        );
    }

    /**
     * Creates a conflict response
     *
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function conflict(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
    {
        return $this->responseFactory->createResponse(
            $this->request,
            HttpStatusCode::Conflict,
            $headers,
            $body
        );
    }

    /**
     * Creates a created response
     *
     * @param string|Uri $uri The location of the created entity
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     * @throws InvalidArgumentException Thrown if the URI was not the correct type
     */
    protected function created(string|Uri $uri, object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
    {
        $headers = $headers ?? new Headers();
        $headers->add('Location', (string)$uri);

        return $this->responseFactory->createResponse(
            $this->request,
            HttpStatusCode::Created,
            $headers,
            $body
        );
    }

    /**
     * Creates a forbidden response
     *
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function forbidden(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
    {
        return $this->responseFactory->createResponse(
            $this->request,
            HttpStatusCode::Forbidden,
            $headers,
            $body
        );
    }

    /**
     * Creates a found redirect response
     *
     * @param string|Uri $uri The URI to redirect to
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws InvalidArgumentException Thrown if the URI is not a string nor a URI
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     * @throws InvalidArgumentException Thrown if the URI is not a string nor an instance of Uri
     */
    protected function found(string|Uri $uri, object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
    {
        return $this->redirect(HttpStatusCode::Found, $uri, $body, $headers);
    }

    /**
     * Creates an internal server error response
     *
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function internalServerError(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
    {
        return $this->responseFactory->createResponse(
            $this->request,
            HttpStatusCode::InternalServerError,
            $headers,
            $body
        );
    }

    /**
     * Creates a moved permanently redirect response
     *
     * @param string|Uri $uri The URI to redirect to
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws InvalidArgumentException Thrown if the URI is not a string nor a URI
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function movedPermanently(string|Uri $uri, object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
    {
        return $this->redirect(HttpStatusCode::MovedPermanently, $uri, $body, $headers);
    }

    /**
     * Creates a no content response
     *
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function noContent(?Headers $headers = null): IResponse
    {
        return $this->responseFactory->createResponse(
            $this->request,
            HttpStatusCode::NoContent,
            $headers
        );
    }

    /**
     * Creates a not found response
     *
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function notFound(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
    {
        return $this->responseFactory->createResponse(
            $this->request,
            HttpStatusCode::NotFound,
            $headers,
            $body
        );
    }

    /**
     * Creates an OK response
     *
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function ok(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
    {
        return $this->responseFactory->createResponse(
            $this->request,
            HttpStatusCode::Ok,
            $headers,
            $body
        );
    }

    /**
     * Reads the request body as a particular type
     *
     * @param string $type The type to read as (should end with '[]' if it's an array of a type)
     * @return mixed The body converted to the input type
     * @throws HttpException Thrown if there was any error with content negotiation
     * @throws LogicException Thrown if the request is not set
     */
    protected function readRequestBodyAs(string $type): mixed
    {
        try {
            return $this->bodyDeserializer->readRequestBodyAs($type, $this->request);
        } catch (FailedContentNegotiationException $ex) {
            throw new HttpException(
                HttpStatusCode::UnsupportedMediaType,
                "Failed to negotiate request content with type $type"
            );
        } catch (SerializationException $ex) {
            throw new HttpException(
                HttpStatusCode::UnprocessableEntity,
                "Failed to deserialize request body when resolving body as type $type",
                0,
                $ex
            );
        }
    }

    /**
     * Creates an unauthorized response
     *
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function unauthorized(object|string|int|float|array|null $body = null, ?Headers $headers = null): IResponse
    {
        return $this->responseFactory->createResponse(
            $this->request,
            HttpStatusCode::Unauthorized,
            $headers,
            $body
        );
    }

    /**
     * Creates a redirect redirect response
     *
     * @param HttpStatusCode|int $statusCode The redirect status code to use
     * @param string|Uri $uri The URI to redirect to
     * @param object|string|int|float|array|null $body The raw response body
     * @param Headers|null $headers The headers to use
     * @return IResponse The response
     * @throws InvalidArgumentException Thrown if the URI is not a string nor a URI
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    private function redirect(
        HttpStatusCode|int $statusCode,
        string|Uri $uri, object|string|int|float|array|null $body = null,
        ?Headers $headers = null
    ): IResponse {
        if (\is_string($uri)) {
            $uriString = $uri;
        } else {
            $uriString = (string)$uri;
        }

        $response = $this->responseFactory->createResponse(
            $this->request,
            $statusCode,
            $headers,
            $body
        );
        $response->headers->add('Location', $uriString);

        return $response;
    }
}
