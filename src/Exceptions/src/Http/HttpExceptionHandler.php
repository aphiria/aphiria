<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Http;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Api\Errors\ProblemDetailsResponseMutator;
use Aphiria\Exceptions\IExceptionHandler;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\IResponseWriter;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StreamBody;
use Aphiria\Net\Http\StreamResponseWriter;
use Closure;
use Exception;

/**
 * Defines the exception handler for HTTP-based apps
 */
class HttpExceptionHandler implements IExceptionHandler
{
    /** @var bool Whether or not to use problem details */
    protected bool $useProblemDetails;
    /** @var IHttpRequestMessage|null The current request, if there is one */
    protected ?IHttpRequestMessage $request;
    /** @var IResponseFactory|null The optional response factory */
    protected ?IResponseFactory $responseFactory;
    /** @var Closure[] The mapping of exception types to closures that return responses */
    protected array $responseFactories = [];
    /** @var IResponseWriter What is used to write the response */
    protected IResponseWriter $responseWriter;

    /**
     * @param bool $useProblemDetails Whether or not to use problem details
     * @param IHttpRequestMessage|null $request The current request, if there is one
     * @param IResponseFactory|null $responseFactory The optional response factory
     * @param IResponseWriter|null $responseWriter What is used to write the response
     */
    public function __construct(
        bool $useProblemDetails = true,
        IHttpRequestMessage $request = null,
        IResponseFactory $responseFactory = null,
        IResponseWriter $responseWriter = null
    ) {
        $this->useProblemDetails = $useProblemDetails;
        $this->request = $request;
        $this->responseFactory = $responseFactory;
        $this->responseWriter = $responseWriter ?? new StreamResponseWriter();
    }

    /**
     * @inheritdoc
     */
    public function handle(Exception $ex): void
    {
        try {
            if ($this->request === null) {
                $response = $this->createResponseWithoutRequest($ex);
            } else {
                $response = $this->createResponseWithRequest($ex, $this->request);
            }
        } catch (Exception $ex) {
            $response = $this->createDefaultResponse($ex);
        }

        $this->responseWriter->writeResponse($response);
    }

    /**
     * Registers many factories for exceptions
     *
     * @param Closure[] $exceptionTypesToFactories The mapping of exception types to factories
     */
    public function registerManyResponseFactories(array $exceptionTypesToFactories): void
    {
        foreach ($exceptionTypesToFactories as $exceptionType => $factory) {
            $this->registerResponseFactory($exceptionType, $factory);
        }
    }

    /**
     * Registers a factory for a specific type of exception
     *
     * @param string $exceptionType The type of exception whose factory we're registering
     * @param Closure $factory The factory that takes in an instance of the exception, the request, and the response factory
     */
    public function registerResponseFactory(string $exceptionType, Closure $factory): void
    {
        $this->responseFactories[$exceptionType] = $factory;
    }

    /**
     * Sets the response factory
     *
     * @param IResponseFactory $responseFactory The response factory to set
     */
    public function setResponseFactory(IResponseFactory $responseFactory): void
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Sets the current request in case it wasn't initially available
     *
     * @param IHttpRequestMessage $request The current request
     */
    public function setRequest(IHttpRequestMessage $request): void
    {
        $this->request = $request;
    }

    /**
     * Creates the default error response
     * Note: It is very important that this method never throws an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return IHttpResponseMessage The created response
     */
    protected function createDefaultResponse(Exception $ex): IHttpResponseMessage
    {
        return new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Creates problem details from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return ProblemDetails The problem details
     */
    protected function createProblemDetails(Exception $ex): ProblemDetails
    {
        return new ProblemDetails(
            'https://tools.ietf.org/html/rfc7231#section-6.6.1',
            'An error occurred',
            null,
            HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    /**
     * Creates a problem details response
     *
     * @param Exception $ex The exception that was thrown
     * @return IHttpResponseMessage The created response
     * @throws SerializationException Thrown if the problem details could not be serialized
     * @throws HttpException Thrown if the response could not be created
     */
    protected function createProblemDetailsResponse(Exception $ex): IHttpResponseMessage
    {
        // Try to take advantage of the response factory
        if ($this->responseFactory !== null && $this->request !== null) {
            $response = $this->responseFactory->createResponse(
                $this->request,
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                null,
                $this->createProblemDetails($ex)
            );

            return (new ProblemDetailsResponseMutator)->mutateResponse($response);
        }

        // We have to manually create a response
        $response = new Response(HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR);
        $response->getHeaders()->add('Content-Type', 'application/problem+json');
        $bodyStream = new Stream(fopen('php://temp', 'r+b'));
        // Intentionally using the parameterless constructor so that the default object encoder gets registered
        $mediaTypeFormatter = new JsonMediaTypeFormatter();
        $mediaTypeFormatter->writeToStream($this->createProblemDetails($ex), $bodyStream, null);
        $response->setBody(new StreamBody($bodyStream));

        return $response;
    }

    /**
     * Creates a response without a request context
     *
     * @param Exception $ex The exception that was thrown
     * @return IHttpResponseMessage The creates request
     * @throws HttpException Thrown if the response could not be created
     * @throws SerializationException Thrown if the problem details could not be serialized
     */
    protected function createResponseWithoutRequest(Exception $ex): IHttpResponseMessage
    {
        if ($this->useProblemDetails) {
            return $this->createProblemDetailsResponse($ex);
        }

        return $this->createDefaultResponse($ex);
    }

    /**
     * Creates a response with a request context
     *
     * @param Exception $ex The exception that was thrown
     * @param IHttpRequestMessage $request The current request
     * @return IHttpResponseMessage The creates request
     * @throws HttpException Thrown if the response could not be created
     * @throws SerializationException Thrown if the problem details could not be serialized
     */
    protected function createResponseWithRequest(Exception $ex, IHttpRequestMessage $request): IHttpResponseMessage
    {
        if ($this->responseFactory !== null && isset($this->responseFactories[\get_class($ex)])) {
            return $this->responseFactories[\get_class($ex)]($ex);
        }

        // We can't do much even with the request if we cannot negotiate it
        return $this->createResponseWithoutRequest($ex);
    }
}
