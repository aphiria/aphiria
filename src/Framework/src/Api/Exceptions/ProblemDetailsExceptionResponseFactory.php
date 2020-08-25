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
use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StreamBody;
use Exception;

/**
 * Defines the exception response factory that creates problem details responses
 * @link https://tools.ietf.org/html/rfc7807
 */
class ProblemDetailsExceptionResponseFactory implements IExceptionResponseFactory
{
    /**
     * @inheritdoc
     */
    public function createResponseWithContext(Exception $ex, IRequest $request, IResponseFactory $responseFactory): IResponse
    {
        // TODO: Implement createResponse() method.
        // TODO: Need to make sure that I use the same status code as the one in the problem details (should grab it from there)
    }

    /**
     * @inheritdoc
     */
    public function createResponseWithoutContext(Exception $ex): IResponse
    {
        // TODO: Implement createResponseWithoutRequest() method.
        // TODO: Need to make sure that I use the same status code as the one in the problem details (should grab it from there)
    }

    /**
     * Maps an exception type to an HTTP status code
     *
     * @param string $exceptionType The exception type to map
     * @param int $statusCode The HTTP status code
     */
    public function mapExceptionToStatusCode(string $exceptionType, int $statusCode): void
    {
        // TODO: Implement mapExceptionToStatusCode() method.
        // Todo: Can I make this even more generic, and map additional problem details properties, eg a dictionary?  How would serialization work for this?  Would Symfony allow serialization of magic properties?
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
     * @param IRequest|null $request The current request, or null if not set
     * @param IResponseFactory|null $responseFactory The response factory, or null if not set
     * @return IResponse The created response
     * @throws SerializationException Thrown if the problem details could not be serialized
     * @throws HttpException Thrown if the response could not be created
     */
    protected function createProblemDetailsResponse(Exception $ex, ?IRequest $request, ?IResponseFactory $responseFactory): IResponse
    {
        // Try to take advantage of the response factory
        if ($responseFactory !== null && $request !== null) {
            $response = $responseFactory->createResponse(
                $request,
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
}
