<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Exceptions;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Api\Errors\ProblemDetailsResponseMutator;
use Aphiria\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\IResponseWriter;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StreamBody;
use Aphiria\Net\Http\StreamResponseWriter;
use Closure;
use Exception;

/**
 * Defines the exception renderer for API applications
 */
class ApiExceptionRenderer implements IApiExceptionRenderer
{
    /** @var bool Whether or not to use problem details */
    protected bool $useProblemDetails;
    /** @var IRequest|null The current request, if there is one */
    protected ?IRequest $request;
    /** @var IResponseFactory|null The optional response factory */
    protected ?IResponseFactory $responseFactory;
    /** @var Closure[] The mapping of exception types to closures that return responses */
    protected array $responseFactories = [];
    /** @var IResponseWriter What is used to write the response */
    protected IResponseWriter $responseWriter;

    /**
     * @param bool $useProblemDetails Whether or not to use problem details
     * @param IRequest|null $request The current request, if there is one
     * @param IResponseFactory|null $responseFactory The optional response factory
     * @param IResponseWriter|null $responseWriter What is used to write the response
     */
    public function __construct(
        bool $useProblemDetails = true,
        IRequest $request = null,
        IResponseFactory $responseFactory = null,
        IResponseWriter $responseWriter = null
    ) {
        $this->useProblemDetails = $useProblemDetails;
        $this->request = $request;
        $this->responseFactory = $responseFactory;
        $this->responseWriter = $responseWriter ?? new StreamResponseWriter();
    }

    /**
     * Creates a response from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return IResponse The response
     */
    public function createResponse(Exception $ex): IResponse
    {
        try {
            if ($this->request === null) {
                return $this->createResponseWithoutRequest($ex);
            }

            return $this->createResponseWithRequest($ex, $this->request);
        } catch (Exception $ex) {
            return $this->createDefaultResponse($ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function render(Exception $ex): void
    {
        $this->responseWriter->writeResponse($this->createResponse($ex));
    }

    /**
     * @inheritdoc
     */
    public function registerManyResponseFactories(array $exceptionTypesToFactories): void
    {
        foreach ($exceptionTypesToFactories as $exceptionType => $factory) {
            $this->registerResponseFactory($exceptionType, $factory);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerResponseFactory(string $exceptionType, Closure $factory): void
    {
        $this->responseFactories[$exceptionType] = $factory;
    }

    /**
     * @inheritdoc
     */
    public function setRequest(IRequest $request): void
    {
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function setResponseFactory(IResponseFactory $responseFactory): void
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * Creates the default error response
     * Note: It is very important that this method never throws an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return IResponse The created response
     */
    protected function createDefaultResponse(Exception $ex): IResponse
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
     * @return IResponse The created response
     * @throws SerializationException Thrown if the problem details could not be serialized
     * @throws HttpException Thrown if the response could not be created
     */
    protected function createProblemDetailsResponse(Exception $ex): IResponse
    {
        // Try to take advantage of the response factory
        if ($this->responseFactory !== null && $this->request !== null) {
            $response = $this->responseFactory->createResponse(
                $this->request,
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                null,
                $this->createProblemDetails($ex)
            );

            return (new ProblemDetailsResponseMutator())->mutateResponse($response);
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
     * @return IResponse The creates request
     * @throws HttpException Thrown if the response could not be created
     * @throws SerializationException Thrown if the problem details could not be serialized
     */
    protected function createResponseWithoutRequest(Exception $ex): IResponse
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
     * @param IRequest $request The current request
     * @return IResponse The creates request
     * @throws HttpException Thrown if the response could not be created
     * @throws SerializationException Thrown if the problem details could not be serialized
     */
    protected function createResponseWithRequest(Exception $ex, IRequest $request): IResponse
    {
        if ($this->responseFactory !== null && isset($this->responseFactories[\get_class($ex)])) {
            return $this->responseFactories[\get_class($ex)]($ex, $request, $this->responseFactory);
        }

        // We can't do much even with the request if we cannot negotiate it
        return $this->createResponseWithoutRequest($ex);
    }
}
