<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\ContentNegotiation;

use Aphiria\IO\Streams\IStream;
use Aphiria\IO\Streams\Stream;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\SerializationException;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpBody;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StreamBody;
use Aphiria\Net\Http\StringBody;
use Aphiria\Serialization\TypeResolver;
use InvalidArgumentException;

/**
 * Defines the factory that generates HTTP responses from negotiated content
 */
final class NegotiatedResponseFactory implements IResponseFactory
{
    /** @var IContentNegotiator The content negotiator to use */
    private IContentNegotiator $contentNegotiator;

    /**
     * @param IContentNegotiator|null $contentNegotiator The content negotiator to use, or null if using the default
     */
    public function __construct(IContentNegotiator $contentNegotiator = null)
    {
        $this->contentNegotiator = $contentNegotiator ?? new ContentNegotiator();
    }

    /**
     * @inheritdoc
     */
    public function createResponse(
        IHttpRequestMessage $request,
        int $statusCode,
        HttpHeaders $headers = null,
        $rawBody = null
    ): IHttpResponseMessage {
        $headers = $headers ?? new HttpHeaders;

        try {
            /** @var ContentNegotiationResult|null $contentNegotiationResult */
            $contentNegotiationResult = null;
            $body = $this->createBody($request, $rawBody, $contentNegotiationResult);
        } catch (InvalidArgumentException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                'Failed to create response body',
                0,
                $ex
            );
        }

        if ($contentNegotiationResult !== null) {
            if (($mediaType = $contentNegotiationResult->getMediaType()) !== null) {
                $headers->add('Content-Type', $mediaType);
            }

            if (($language = $contentNegotiationResult->getLanguage()) !== null) {
                $headers->add('Content-Language', $language);
            }
        }

        if (
            $body !== null
            && !$headers->containsKey('Content-Length')
            && ($contentLength = $body->getLength()) !== null
        ) {
            $headers->add('Content-Length', $contentLength);
        }

        return new Response($statusCode, $headers, $body);
    }

    /**
     * Creates a negotiated response body from a request
     *
     * @param IHttpRequestMessage $request The current request
     * @param object|string|int|float|array|null $rawBody The raw body to use in the response
     * @param ContentNegotiationResult|null $contentNegotiationResult The response content negotiation result
     * @return IHttpBody|null The body if one was created, otherwise null
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws HttpException Thrown if the response content could not be negotiated
     */
    private function createBody(
        IHttpRequestMessage $request,
        $rawBody,
        ContentNegotiationResult &$contentNegotiationResult = null
    ): ?IHttpBody {
        if ($rawBody === null || $rawBody instanceof IHttpBody) {
            return $rawBody;
        }

        if ($rawBody instanceof IStream) {
            return new StreamBody($rawBody);
        }

        if (is_scalar($rawBody)) {
            return new StringBody((string)$rawBody);
        }

        if ((!is_object($rawBody) && !is_array($rawBody)) || is_callable($rawBody)) {
            throw new InvalidArgumentException('Unsupported body type ' . gettype($rawBody));
        }

        $type = TypeResolver::resolveType($rawBody);
        $contentNegotiationResult = $this->contentNegotiator->negotiateResponseContent($type, $request);
        $mediaTypeFormatter = $contentNegotiationResult->getFormatter();

        if ($mediaTypeFormatter === null) {
            throw $this->createNotAcceptableException($type);
        }

        $bodyStream = new Stream(fopen('php://temp', 'r+b'));

        try {
            $mediaTypeFormatter->writeToStream(
                $rawBody,
                $bodyStream,
                $contentNegotiationResult->getEncoding()
            );
        } catch (SerializationException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
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
        $headers = new HttpHeaders();
        $headers->add('Content-Type', 'application/json');
        $body = new StringBody(json_encode($this->contentNegotiator->getAcceptableResponseMediaTypes($type)));
        $response = new Response(HttpStatusCodes::HTTP_NOT_ACCEPTABLE, $headers, $body);

        return new HttpException($response);
    }
}
