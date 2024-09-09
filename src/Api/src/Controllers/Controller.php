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
    /** @var IBodyDeserializer|null The body deserializer */
    protected ?IBodyDeserializer $bodyDeserializer = null;
    /** @var IRequest|null The current request */
    protected ?IRequest $request = null;
    /** @var RequestParser|null The parser to use to get data from the current request */
    protected ?RequestParser $requestParser = null;
    /** @var IResponseFactory|null The response factory */
    protected ?IResponseFactory $responseFactory = null;
    /** @var ResponseFormatter|null The formatter to use to write data to the response */
    protected ?ResponseFormatter $responseFormatter = null;
    /** @var IUserAccessor|null The user accessor */
    protected ?IUserAccessor $userAccessor = null;

    /**
     * Sets the body deserializer
     *
     * @param IBodyDeserializer $bodyDeserializer The body deserializer
     * @internal
     */
    public function setBodyDeserializer(IBodyDeserializer $bodyDeserializer): void
    {
        $this->bodyDeserializer = $bodyDeserializer;
    }

    /**
     * Sets the current request
     *
     * @param IRequest $request The current request
     * @internal
     */
    public function setRequest(IRequest $request): void
    {
        $this->request = $request;
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
     * Sets the response factory
     *
     * @param IResponseFactory $responseFactory The response factory
     * @internal
     */
    public function setResponseFactory(IResponseFactory $responseFactory): void
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Sets the response formatter
     *
     * @param ResponseFormatter $responseFormatter The response formatter
     * @internal
     */
    public function setResponseFormatter(ResponseFormatter $responseFormatter): void
    {
        $this->responseFormatter = $responseFormatter;
    }

    /**
     * Sets the user accessor
     *
     * @param IUserAccessor $userAccessor The user accessor
     * @internal
     */
    public function setUserAccessor(IUserAccessor $userAccessor): void
    {
        $this->userAccessor = $userAccessor;
    }

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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
     * Gets the current authenticated user
     *
     * @return IPrincipal|null The current user if one was set, otherwise null
     * @throws LogicException Thrown if the request or user accessor are not set
     */
    protected function getUser(): ?IPrincipal
    {
        if (!$this->userAccessor instanceof IUserAccessor) {
            throw new LogicException('User accessor is not set');
        }

        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

        return $this->userAccessor->getUser($this->request);
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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
        if (!$this->request instanceof IRequest) {
            throw new LogicException('Request is not set');
        }

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
