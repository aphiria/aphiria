<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Controllers;

use InvalidArgumentException;
use LogicException;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Opulence\Net\Http\Formatting\RequestParser;
use Opulence\Net\Http\Formatting\ResponseFormatter;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Uri;
use Opulence\Serialization\SerializationException;

/**
 * Defines the base class for controllers to extend
 */
class Controller
{
    /** @var IHttpRequestMessage The current request */
    protected $request;
    /** @var RequestParser The parser to use to get data from the current request */
    protected $requestParser;
    /** @var ResponseFormatter The formatter to use to write data to the response */
    protected $responseFormatter;
    /** @var IContentNegotiator The content negotiator */
    protected $contentNegotiator;
    /** @var INegotiatedResponseFactory The negotiated response factory */
    protected $negotiatedResponseFactory;

    /**
     * Sets the content negotiator
     *
     * @param IContentNegotiator $contentNegotiator The content negotiator
     * @internal
     */
    public function setContentNegotiator(IContentNegotiator $contentNegotiator): void
    {
        $this->contentNegotiator = $contentNegotiator;
    }

    /**
     * Sets the negotiated response factory
     *
     * @param INegotiatedResponseFactory $negotiatedResponseFactory The negotiated response factory
     * @internal
     */
    public function setNegotiatedResponseFactory(INegotiatedResponseFactory $negotiatedResponseFactory): void
    {
        $this->negotiatedResponseFactory = $negotiatedResponseFactory;
    }

    /**
     * Sets the current request
     *
     * @param IHttpRequestMessage $request The current request
     * @internal
     */
    public function setRequest(IHttpRequestMessage $request): void
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
     * Creates a bad request response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function badRequest($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        return $this->negotiatedResponseFactory->createResponse(
            $this->request,
            HttpStatusCodes::HTTP_BAD_REQUEST,
            $headers,
            $body
        );
    }

    /**
     * Creates a conflict response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function conflict($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        return $this->negotiatedResponseFactory->createResponse(
            $this->request,
            HttpStatusCodes::HTTP_CONFLICT,
            $headers,
            $body
        );
    }

    /**
     * Creates a created response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function created($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        return $this->negotiatedResponseFactory->createResponse(
            $this->request,
            HttpStatusCodes::HTTP_CREATED,
            $headers,
            $body
        );
    }

    /**
     * Creates a forbidden response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function forbidden($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        return $this->negotiatedResponseFactory->createResponse(
            $this->request,
            HttpStatusCodes::HTTP_FORBIDDEN,
            $headers,
            $body
        );
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
     * @throws LogicException Thrown if the request is not set
     * @throws InvalidArgumentException Thrown if the URI is not a string nor an instance of Uri
     */
    protected function found($uri, $body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        if (\is_string($uri)) {
            $uriString = $uri;
        } elseif ($uri instanceof Uri) {
            $uriString = (string)$uri;
        } else {
            throw new InvalidArgumentException('URI must be a string or instance of ' . Uri::class);
        }

        $response = $this->negotiatedResponseFactory->createResponse(
            $this->request,
            HttpStatusCodes::HTTP_FOUND,
            $headers,
            $body
        );
        $response->getHeaders()->add('Location', $uriString);

        return $response;
    }

    /**
     * Creates an internal server error response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function internalServerError($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        return $this->negotiatedResponseFactory->createResponse(
            $this->request,
            HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
            $headers,
            $body
        );
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
     * @throws LogicException Thrown if the request is not set
     */
    protected function movedPermanently($uri, $body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        if (\is_string($uri)) {
            $uriString = $uri;
        } elseif ($uri instanceof Uri) {
            $uriString = (string)$uri;
        } else {
            throw new InvalidArgumentException('URI must be a string or instance of ' . Uri::class);
        }

        $response = $this->negotiatedResponseFactory->createResponse(
            $this->request,
            HttpStatusCodes::HTTP_MOVED_PERMANENTLY,
            $headers,
            $body
        );
        $response->getHeaders()->add('Location', $uriString);

        return $response;
    }

    /**
     * Creates a no content response
     *
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function noContent(HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        return $this->negotiatedResponseFactory->createResponse(
            $this->request,
            HttpStatusCodes::HTTP_NO_CONTENT,
            $headers,
            null
        );
    }

    /**
     * Creates a not found response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function notFound($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        return $this->negotiatedResponseFactory->createResponse(
            $this->request,
            HttpStatusCodes::HTTP_NOT_FOUND,
            $headers,
            $body
        );
    }

    /**
     * Creates an OK response
     *
     * @param \object|string|int|float|array|null $body The raw response body
     * @param HttpHeaders|null $headers The headers to use
     * @return IHttpResponseMessage The response
     * @throws HttpException Thrown if there was an error creating the response
     * @throws LogicException Thrown if the request is not set
     */
    protected function ok($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        return $this->negotiatedResponseFactory->createResponse(
            $this->request,
            HttpStatusCodes::HTTP_OK,
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
    protected function readRequestBodyAs(string $type)
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        $contentNegotiationResult = $this->contentNegotiator->negotiateRequestContent($type, $this->request);
        $mediaTypeFormatter = $contentNegotiationResult->getFormatter();

        if ($mediaTypeFormatter === null) {
            throw new HttpException(
                HttpStatusCodes::HTTP_UNSUPPORTED_MEDIA_TYPE,
                "Failed to negotiate request content with type $type"
            );
        }

        if (($body = $this->request->getBody()) === null) {
            return [];
        }

        try {
            return $mediaTypeFormatter->readFromStream($body->readAsStream(), $type);
        } catch (SerializationException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_UNPROCESSABLE_ENTITY,
                "Failed to deserialize request body when resolving body as type $type"
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
     * @throws LogicException Thrown if the request is not set
     */
    protected function unauthorized($body = null, HttpHeaders $headers = null): IHttpResponseMessage
    {
        if (!$this->request instanceof IHttpRequestMessage) {
            throw new LogicException('Request is not set');
        }

        return $this->negotiatedResponseFactory->createResponse(
            $this->request,
            HttpStatusCodes::HTTP_UNAUTHORIZED,
            $headers,
            $body
        );
    }
}
