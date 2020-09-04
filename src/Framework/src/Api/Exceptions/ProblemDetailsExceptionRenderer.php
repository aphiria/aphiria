<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Api\Exceptions;

use Aphiria\Api\Errors\ProblemDetails;
use Aphiria\Api\Errors\ProblemDetailsResponseMutator;
use Aphiria\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\IO\Streams\Stream;
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
 * Defines the problem details exception renderer for API applications
 */
class ProblemDetailsExceptionRenderer implements IApiExceptionRenderer
{
    /** @var string[] The mapping of HTTP status codes to their RFC type URIs */
    protected static array $statusesToRfcTypes = [
        HttpStatusCodes::HTTP_OK => 'https://tools.ietf.org/html/rfc7231#section-6.3.1',
        HttpStatusCodes::HTTP_CREATED => 'https://tools.ietf.org/html/rfc7231#section-6.3.2',
        HttpStatusCodes::HTTP_ACCEPTED => 'https://tools.ietf.org/html/rfc7231#section-6.3.3',
        HttpStatusCodes::HTTP_NON_AUTHORITATIVE_INFORMATION => 'https://tools.ietf.org/html/rfc7231#section-6.3.4',
        HttpStatusCodes::HTTP_NO_CONTENT => 'https://tools.ietf.org/html/rfc7231#section-6.3.5',
        HttpStatusCodes::HTTP_RESET_CONTENT => 'https://tools.ietf.org/html/rfc7231#section-6.3.6',
        HttpStatusCodes::HTTP_MULTIPLE_CHOICE => 'https://tools.ietf.org/html/rfc7231#section-6.4.1',
        HttpStatusCodes::HTTP_MOVED_PERMANENTLY => 'https://tools.ietf.org/html/rfc7231#section-6.4.2',
        HttpStatusCodes::HTTP_FOUND => 'https://tools.ietf.org/html/rfc7231#section-6.4.3',
        HttpStatusCodes::HTTP_SEE_OTHER => 'https://tools.ietf.org/html/rfc7231#section-6.4.4',
        HttpStatusCodes::HTTP_USE_PROXY => 'https://tools.ietf.org/html/rfc7231#section-6.4.5',
        HttpStatusCodes::HTTP_TEMPORARY_REDIRECT => 'https://tools.ietf.org/html/rfc7231#section-6.4.7',
        HttpStatusCodes::HTTP_BAD_REQUEST => 'https://tools.ietf.org/html/rfc7231#section-6.5.1',
        HttpStatusCodes::HTTP_PAYMENT_REQUIRED => 'https://tools.ietf.org/html/rfc7231#section-6.5.2',
        HttpStatusCodes::HTTP_FORBIDDEN => 'https://tools.ietf.org/html/rfc7231#section-6.5.3',
        HttpStatusCodes::HTTP_NOT_FOUND => 'https://tools.ietf.org/html/rfc7231#section-6.5.4',
        HttpStatusCodes::HTTP_METHOD_NOT_ALLOWED => 'https://tools.ietf.org/html/rfc7231#section-6.5.5',
        HttpStatusCodes::HTTP_NOT_ACCEPTABLE => 'https://tools.ietf.org/html/rfc7231#section-6.5.6',
        HttpStatusCodes::HTTP_REQUEST_TIMEOUT => 'https://tools.ietf.org/html/rfc7231#section-6.5.7',
        HttpStatusCodes::HTTP_CONFLICT => 'https://tools.ietf.org/html/rfc7231#section-6.5.8',
        HttpStatusCodes::HTTP_GONE => 'https://tools.ietf.org/html/rfc7231#section-6.5.9',
        HttpStatusCodes::HTTP_LENGTH_REQUIRED => 'https://tools.ietf.org/html/rfc7231#section-6.5.10',
        HttpStatusCodes::HTTP_REQUEST_ENTITY_TOO_LARGE => 'https://tools.ietf.org/html/rfc7231#section-6.5.11',
        HttpStatusCodes::HTTP_URI_TOO_LONG => 'https://tools.ietf.org/html/rfc7231#section-6.5.12',
        HttpStatusCodes::HTTP_UNSUPPORTED_MEDIA_TYPE => 'https://tools.ietf.org/html/rfc7231#section-6.5.13',
        HttpStatusCodes::HTTP_EXPECTATION_FAILED => 'https://tools.ietf.org/html/rfc7231#section-6.5.14',
        HttpStatusCodes::HTTP_UPGRADE_REQUIRED => 'https://tools.ietf.org/html/rfc7231#section-6.5.15',
        HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR => 'https://tools.ietf.org/html/rfc7231#section-6.6.1',
        HttpStatusCodes::HTTP_NOT_IMPLEMENTED => 'https://tools.ietf.org/html/rfc7231#section-6.6.2',
        HttpStatusCodes::HTTP_BAD_GATEWAY => 'https://tools.ietf.org/html/rfc7231#section-6.6.3',
        HttpStatusCodes::HTTP_SERVICE_UNAVAILABLE => 'https://tools.ietf.org/html/rfc7231#section-6.6.4',
        HttpStatusCodes::HTTP_GATEWAY_TIMEOUT => 'https://tools.ietf.org/html/rfc7231#section-6.6.5',
        HttpStatusCodes::HTTP_HTTP_VERSION_NOT_SUPPORTED => 'https://tools.ietf.org/html/rfc7231#section-6.6.6'
    ];
    /** @var Closure[] The mapping of exception types to problem details factories */
    protected array $exceptionTypesToProblemDetailsFactories = [];
    /** @var IRequest|null The current request, if there is one */
    protected ?IRequest $request;
    /** @var IResponseFactory|null The optional response factory */
    protected ?IResponseFactory $responseFactory;
    /** @var IResponseWriter What is used to write the response */
    protected IResponseWriter $responseWriter;

    /**
     * @param IRequest|null $request The current request, if there is one
     * @param IResponseFactory|null $responseFactory The optional response factory
     * @param IResponseWriter|null $responseWriter What is used to write the response
     */
    public function __construct(
        IRequest $request = null,
        IResponseFactory $responseFactory = null,
        IResponseWriter $responseWriter = null
    ) {
        $this->request = $request;
        $this->responseFactory = $responseFactory;
        $this->responseWriter = $responseWriter ?? new StreamResponseWriter();
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
                $bodyStream = new Stream(fopen('php://temp', 'r+b'));
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
     * @param string $exceptionType The type of exception that was thrown
     * @param string|Closure|null $type The optional problem details type, or a closure that takes in the exception and returns a type, or null
     * @param string|Closure|null $title The optional problem details title, or a closure that takes in the exception and returns a title, or null
     * @param string|Closure|null $detail The optional problem details detail, or a closure that takes in the exception and returns a detail, or null
     * @param int|Closure|null $status The optional problem details status, or a closure that takes in the exception and returns a type, or null
     * @param string|Closure|null $instance The optional problem details instance, or a closure that takes in the exception and returns an instance, or null
     * @param array|Closure|null $extensions The optional problem details extensions, or a closure that takes in the exception and returns an exception, or null
     */
    public function mapExceptionToProblemDetails(
        string $exceptionType,
        $type = null,
        $title = null,
        $detail = null,
        $status = null,
        $instance = null,
        $extensions = null
    ): void {
        // TODO: Add an Aphiria component for adding these mappings
        $this->exceptionTypesToProblemDetailsFactories[$exceptionType] = function (Exception $ex) use ($type, $title, $detail, $status, $instance, $extensions) {
            if (\is_callable($status)) {
                $status = $status($ex);
            } elseif ($status === null) {
                $status = HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR;
            }

            if (\is_callable($type)) {
                $type = $type($ex);
            } elseif ($type === null) {
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
        $exceptionType = \get_class($ex);

        if (isset($this->exceptionTypesToProblemDetailsFactories[$exceptionType])) {
            return $this->exceptionTypesToProblemDetailsFactories[$exceptionType]($ex);
        }

        return new ProblemDetails(
            $this->getTypeFromException($ex, HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR),
            $this->getTitleFromException($ex),
            $this->getDetailFromException($ex),
            HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
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
        return self::$statusesToRfcTypes[$statusCode ?? HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR] ?? null;
    }
}
