<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Exceptions;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Api\Errors\ProblemDetailsResponseMutator;
use Aphiria\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\HttpStatusCode;
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
 * Defines the problem details exception renderer for API applications
 */
class ProblemDetailsExceptionRenderer implements IApiExceptionRenderer
{
    /** @var array<int, string> The mapping of HTTP status codes to their RFC type URIs */
    protected static array $statusesToRfcTypes = [
        HttpStatusCode::OK => 'https://tools.ietf.org/html/rfc7231#section-6.3.1',
        HttpStatusCode::CREATED => 'https://tools.ietf.org/html/rfc7231#section-6.3.2',
        HttpStatusCode::ACCEPTED => 'https://tools.ietf.org/html/rfc7231#section-6.3.3',
        HttpStatusCode::NON_AUTHORITATIVE_INFORMATION => 'https://tools.ietf.org/html/rfc7231#section-6.3.4',
        HttpStatusCode::NO_CONTENT => 'https://tools.ietf.org/html/rfc7231#section-6.3.5',
        HttpStatusCode::RESET_CONTENT => 'https://tools.ietf.org/html/rfc7231#section-6.3.6',
        HttpStatusCode::PARTIAL_CONTENT => 'https://tools.ietf.org/html/rfc7233#section-4.1',
        HttpStatusCode::MULTIPLE_CHOICE => 'https://tools.ietf.org/html/rfc7231#section-6.4.1',
        HttpStatusCode::MOVED_PERMANENTLY => 'https://tools.ietf.org/html/rfc7231#section-6.4.2',
        HttpStatusCode::FOUND => 'https://tools.ietf.org/html/rfc7231#section-6.4.3',
        HttpStatusCode::SEE_OTHER => 'https://tools.ietf.org/html/rfc7231#section-6.4.4',
        HttpStatusCode::NOT_MODIFIED => 'https://tools.ietf.org/html/rfc7232#section-4.1',
        HttpStatusCode::USE_PROXY => 'https://tools.ietf.org/html/rfc7231#section-6.4.5',
        HttpStatusCode::TEMPORARY_REDIRECT => 'https://tools.ietf.org/html/rfc7231#section-6.4.7',
        HttpStatusCode::BAD_REQUEST => 'https://tools.ietf.org/html/rfc7231#section-6.5.1',
        HttpStatusCode::UNAUTHORIZED => 'https://tools.ietf.org/html/rfc7235#section-3.1',
        HttpStatusCode::PAYMENT_REQUIRED => 'https://tools.ietf.org/html/rfc7231#section-6.5.2',
        HttpStatusCode::FORBIDDEN => 'https://tools.ietf.org/html/rfc7231#section-6.5.3',
        HttpStatusCode::NOT_FOUND => 'https://tools.ietf.org/html/rfc7231#section-6.5.4',
        HttpStatusCode::METHOD_NOT_ALLOWED => 'https://tools.ietf.org/html/rfc7231#section-6.5.5',
        HttpStatusCode::NOT_ACCEPTABLE => 'https://tools.ietf.org/html/rfc7231#section-6.5.6',
        HttpStatusCode::PROXY_AUTHENTICATION_REQUIRED => 'https://tools.ietf.org/html/rfc7235#section-3.2',
        HttpStatusCode::REQUEST_TIMEOUT => 'https://tools.ietf.org/html/rfc7231#section-6.5.7',
        HttpStatusCode::CONFLICT => 'https://tools.ietf.org/html/rfc7231#section-6.5.8',
        HttpStatusCode::GONE => 'https://tools.ietf.org/html/rfc7231#section-6.5.9',
        HttpStatusCode::LENGTH_REQUIRED => 'https://tools.ietf.org/html/rfc7231#section-6.5.10',
        HttpStatusCode::PRECONDITION_FAILED => 'https://tools.ietf.org/html/rfc7232#section-4.2',
        HttpStatusCode::REQUEST_ENTITY_TOO_LARGE => 'https://tools.ietf.org/html/rfc7231#section-6.5.11',
        HttpStatusCode::URI_TOO_LONG => 'https://tools.ietf.org/html/rfc7231#section-6.5.12',
        HttpStatusCode::UNSUPPORTED_MEDIA_TYPE => 'https://tools.ietf.org/html/rfc7231#section-6.5.13',
        HttpStatusCode::REQUESTED_RANGE_NOT_SATISFIABLE => 'https://tools.ietf.org/html/rfc7233#section-4.4',
        HttpStatusCode::EXPECTATION_FAILED => 'https://tools.ietf.org/html/rfc7231#section-6.5.14',
        HttpStatusCode::UPGRADE_REQUIRED => 'https://tools.ietf.org/html/rfc7231#section-6.5.15',
        HttpStatusCode::INTERNAL_SERVER_ERROR => 'https://tools.ietf.org/html/rfc7231#section-6.6.1',
        HttpStatusCode::NOT_IMPLEMENTED => 'https://tools.ietf.org/html/rfc7231#section-6.6.2',
        HttpStatusCode::BAD_GATEWAY => 'https://tools.ietf.org/html/rfc7231#section-6.6.3',
        HttpStatusCode::SERVICE_UNAVAILABLE => 'https://tools.ietf.org/html/rfc7231#section-6.6.4',
        HttpStatusCode::GATEWAY_TIMEOUT => 'https://tools.ietf.org/html/rfc7231#section-6.6.5',
        HttpStatusCode::HTTP_VERSION_NOT_SUPPORTED => 'https://tools.ietf.org/html/rfc7231#section-6.6.6'
    ];
    /** @var array<class-string, Closure(Exception): ProblemDetails> The mapping of exception types to problem details factories */
    protected array $exceptionTypesToProblemDetailsFactories = [];

    /**
     * @param IRequest|null $request The current request, if there is one
     * @param IResponseFactory|null $responseFactory The optional response factory
     * @param IResponseWriter $responseWriter What is used to write the response
     */
    public function __construct(
        protected ?IRequest $request = null,
        protected ?IResponseFactory $responseFactory = null,
        protected readonly IResponseWriter $responseWriter = new StreamResponseWriter()
    ) {
    }

    /**
     * @inheritdoc
     */
    public function createResponse(Exception $ex): IResponse
    {
        try {
            $problemDetails = $this->createProblemDetails($ex);

            if ($this->request === null || $this->responseFactory === null) {
                // We have to manually create a response
                $response = new Response($problemDetails->status);
                $response->getHeaders()->add('Content-Type', 'application/problem+json');
                $bodyStream = new Stream(\fopen('php://temp', 'r+b'));
                // Intentionally using the parameterless constructor so that the default object encoder gets registered
                $mediaTypeFormatter = new JsonMediaTypeFormatter();
                $mediaTypeFormatter->writeToStream($problemDetails, $bodyStream, null);
                $response->setBody(new StreamBody($bodyStream));

                return $response;
            }

            $response = $this->responseFactory->createResponse(
                $this->request,
                $problemDetails->status,
                null,
                $problemDetails
            );

            return (new ProblemDetailsResponseMutator())->mutateResponse($response);
        } catch (Exception $ex) {
            return $this->createDefaultResponse($ex);
        }
    }

    /**
     * Maps an exception type to problem details properties
     *
     * @param class-string $exceptionType The type of exception that was thrown
     * @param string|null|Closure(mixed): string $type The optional problem details type, or a closure that takes in the exception and returns a type, or null
     * @param string|null|Closure(mixed): string $title The optional problem details title, or a closure that takes in the exception and returns a title, or null
     * @param string|null|Closure(mixed): string $detail The optional problem details detail, or a closure that takes in the exception and returns a detail, or null
     * @param int|Closure(mixed): int $status The optional problem details status, or a closure that takes in the exception and returns a type, or null
     * @param string|null|Closure(mixed): string $instance The optional problem details instance, or a closure that takes in the exception and returns an instance, or null
     * @param array|null|Closure(mixed): array $extensions The optional problem details extensions, or a closure that takes in the exception and returns an exception, or null
     */
    public function mapExceptionToProblemDetails(
        string $exceptionType,
        string|Closure $type = null,
        string|Closure $title = null,
        string|Closure $detail = null,
        int|Closure $status = HttpStatusCode::INTERNAL_SERVER_ERROR,
        string|Closure $instance = null,
        array|Closure $extensions = null
    ): void {
        $this->exceptionTypesToProblemDetailsFactories[$exceptionType] = function (Exception $ex) use ($type, $title, $detail, $status, $instance, $extensions): ProblemDetails {
            if (\is_callable($status)) {
                $status = $status($ex);
            }

            if (\is_callable($type)) {
                $type = $type($ex);
            } elseif ($type === null) {
                /** @psalm-suppress PossiblyInvalidArgument The status will always be an int */
                $type = $this->getTypeFromException($ex, $status);
            }

            if (\is_callable($title)) {
                $title = $title($ex);
            } elseif ($title === null) {
                $title = $this->getTitleFromException($ex);
            }

            if (\is_callable($detail)) {
                $detail = $detail($ex);
            } elseif ($detail === null) {
                $detail = $this->getDetailFromException($ex);
            }

            if (\is_callable($instance)) {
                $instance = $instance($ex);
            } elseif ($instance === null) {
                $instance = $this->getInstanceFromException($ex);
            }

            if (\is_callable($extensions)) {
                $extensions = $extensions($ex);
            }

            /** @psalm-suppress PossiblyInvalidArgument The types here are all correct */
            return new ProblemDetails($type, $title, $detail, $status, $instance, $extensions);
        };
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
        return new Response(HttpStatusCode::INTERNAL_SERVER_ERROR);
    }

    /**
     * Creates problem details from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return ProblemDetails The problem details
     */
    protected function createProblemDetails(Exception $ex): ProblemDetails
    {
        $exceptionType = $ex::class;

        if (isset($this->exceptionTypesToProblemDetailsFactories[$exceptionType])) {
            return $this->exceptionTypesToProblemDetailsFactories[$exceptionType]($ex);
        }

        return new ProblemDetails(
            $this->getTypeFromException($ex, HttpStatusCode::INTERNAL_SERVER_ERROR),
            $this->getTitleFromException($ex),
            $this->getDetailFromException($ex),
            HttpStatusCode::INTERNAL_SERVER_ERROR,
            $this->getInstanceFromException($ex)
        );
    }

    /**
     * Gets the problem details detail from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return string|null The details if one is to be used, otherwise null
     */
    protected function getDetailFromException(Exception $ex): ?string
    {
        return null;
    }

    /**
     * Gets the problem details instance from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return string|null The instance if one is to be used, otherwise null
     */
    protected function getInstanceFromException(Exception $ex): ?string
    {
        return null;
    }

    /**
     * Gets the problem details title from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @return string|null The title if one is to be used, otherwise null
     */
    protected function getTitleFromException(Exception $ex): ?string
    {
        return $ex->getMessage();
    }

    /**
     * Gets the problem details type from an exception
     *
     * @param Exception $ex The exception that was thrown
     * @param int|null $statusCode The HTTP status code if one was set, otherwise null
     * @return string|null The type if one is to be used, otherwise nul
     */
    protected function getTypeFromException(Exception $ex, ?int $statusCode): ?string
    {
        return self::$statusesToRfcTypes[$statusCode ?? HttpStatusCode::INTERNAL_SERVER_ERROR] ?? null;
    }
}
