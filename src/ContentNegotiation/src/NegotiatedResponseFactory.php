<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation;

use Aphiria\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\IO\Streams\IStream;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StreamBody;
use Aphiria\Net\Http\StringBody;
use Aphiria\Reflection\TypeResolver;
use InvalidArgumentException;
use JsonException;

/**
 * Defines the factory that generates HTTP responses from negotiated content
 */
final class NegotiatedResponseFactory implements IResponseFactory
{
    /**
     * @param IContentNegotiator $contentNegotiator The content negotiator to use
     */
    public function __construct(private readonly IContentNegotiator $contentNegotiator = new ContentNegotiator())
    {
    }

    /**
     * @inheritdoc
     */
    public function createResponse(
        IRequest $request,
        HttpStatusCode|int $statusCode,
        ?Headers $headers = null,
        object|string|int|float|array|null $rawBody = null
    ): IResponse {
        $headers = $headers ?? new Headers();

        try {
            /** @var ContentNegotiationResult|null $contentNegotiationResult */
            $contentNegotiationResult = null;
            $body = $this->createBody($request, $rawBody, $contentNegotiationResult);
        } catch (InvalidArgumentException $ex) {
            throw new HttpException(
                HttpStatusCode::InternalServerError,
                'Failed to create response body',
                0,
                $ex
            );
        }

        if ($contentNegotiationResult !== null) {
            if (($mediaType = $contentNegotiationResult->mediaType) !== null) {
                $headers->add('Content-Type', $mediaType);
            }

            if (($language = $contentNegotiationResult->language) !== null) {
                $headers->add('Content-Language', $language);
            }
        }

        if (
            $body !== null
            && !$headers->containsKey('Content-Length')
            && ($contentLength = $body->length) !== null
        ) {
            $headers->add('Content-Length', $contentLength);
        }

        return new Response($statusCode, $headers, $body);
    }

    /**
     * Creates a negotiated response body from a request
     *
     * @param IRequest $request The current request
     * @param object|string|int|float|array|null $rawBody The raw body to use in the response
     * @param ContentNegotiationResult|null $contentNegotiationResult The response content negotiation result
     * @return IBody|null The body if one was created, otherwise null
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws HttpException Thrown if the response content could not be negotiated
     */
    private function createBody(
        IRequest $request,
        object|string|int|float|array|null $rawBody = null,
        ?ContentNegotiationResult &$contentNegotiationResult = null
    ): ?IBody {
        if ($rawBody === null || $rawBody instanceof IBody) {
            return $rawBody;
        }

        if ($rawBody instanceof IStream) {
            return new StreamBody($rawBody);
        }

        if (\is_scalar($rawBody)) {
            return new StringBody((string)$rawBody);
        }

        if ((!\is_object($rawBody) && !\is_array($rawBody)) || \is_callable($rawBody)) {
            throw new InvalidArgumentException('Unsupported body type ' . \gettype($rawBody));
        }

        $type = TypeResolver::resolveType($rawBody);
        $contentNegotiationResult = $this->contentNegotiator->negotiateResponseContent($type, $request);
        $mediaTypeFormatter = $contentNegotiationResult->formatter;

        if ($mediaTypeFormatter === null) {
            throw $this->createNotAcceptableException($type);
        }

        $bodyStream = new Stream(\fopen('php://temp', 'r+b'));

        try {
            $mediaTypeFormatter->writeToStream(
                $rawBody,
                $bodyStream,
                $contentNegotiationResult->encoding
            );
        } catch (SerializationException $ex) {
            throw new HttpException(
                HttpStatusCode::InternalServerError,
                'Failed to serialize response body',
                0,
                $ex
            );
        }

        return new StreamBody($bodyStream);
    }

    /**
     * Creates a 406 Not Acceptable exception
     *
     * @param string $type The type that was attempted to be written
     * @return HttpException The exception with the response set
     */
    private function createNotAcceptableException(string $type): HttpException
    {
        $headers = new Headers();
        $headers->add('Content-Type', 'application/json');

        try {
            $body = new StringBody(\json_encode($this->contentNegotiator->getAcceptableResponseMediaTypes($type), JSON_THROW_ON_ERROR));
            // Realistically, we won't ever have an array of strings that cannot be encoded to JSON
            // @codeCoverageIgnoreStart
        } catch (JsonException) {
            $body = null;
            // @codeCoverageIgnoreEnd
        }

        $response = new Response(HttpStatusCode::NotAcceptable, $headers, $body);

        return new HttpException($response);
    }
}
