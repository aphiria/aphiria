<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\ContentNegotiation;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use Opulence\IO\Streams\Stream;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Response;
use Opulence\Net\Http\StreamBody;
use Opulence\Net\Http\StringBody;
use Opulence\Serialization\SerializationException;
use Opulence\Serialization\TypeResolver;

/**
 * Defines the factory that generates HTTP responses from negotiated content
 */
class NegotiatedResponseFactory
{
    /** @var IContentNegotiator The content negotiator to use */
    private $contentNegotiator;

    /**
     * @param IContentNegotiator $contentNegotiator The content negotiator to use
     */
    public function __construct(IContentNegotiator $contentNegotiator)
    {
        $this->contentNegotiator = $contentNegotiator;
    }

    /**
     * Creates a response with a negotiated body
     *
     * @param IHttpRequestMessage $request The current request
     * @param int $statusCode The status code to use
     * @param HttpHeaders|null $headers The headers to use
     * @param \object|string|int|float|array|null $rawBody The raw body to use in the response
     * @return IHttpResponseMessage The created response
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws HttpException Thrown if the response content could not be negotiated
     */
    public function createResponse(
        IHttpRequestMessage $request,
        int $statusCode,
        ?HttpHeaders $headers,
        $rawBody
    ): IHttpResponseMessage {
        $headers = $headers ?? new HttpHeaders;

        try {
            /** @var ContentNegotiationResult|null $responseContentNegotiationResult */
            $responseContentNegotiationResult = null;
            $body = $this->createBody($request, $rawBody, $responseContentNegotiationResult);
        } catch (InvalidArgumentException $ex) {
            throw new HttpException(
                HttpStatusCodes::HTTP_INTERNAL_SERVER_ERROR,
                'Failed to create response body',
                0,
                $ex
            );
        }

        if (
            $responseContentNegotiationResult !== null
            && ($mediaType = $responseContentNegotiationResult->getMediaType()) !== null
        ) {
            $headers->add('Content-Type', $mediaType);
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
     * @param \object|string|int|float|array|null $rawBody The raw body to use in the response
     * @param ContentNegotiationResult|null $responseContentNegotiationResult The response content negotiation result
     * @return IHttpBody|null The body if one was created, otherwise null
     * @throws InvalidArgumentException Thrown if the body is not a supported type
     * @throws HttpException Thrown if the response content could not be negotiated
     */
    private function createBody(
        IHttpRequestMessage $request,
        $rawBody,
        ContentNegotiationResult &$responseContentNegotiationResult = null
    ): ?IHttpBody {
        if ($rawBody === null || $rawBody instanceof IHttpBody) {
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

        if (\is_array($rawBody)) {
            if (\count($rawBody) === 0) {
                return null;
            }

            $responseContentNegotiationResult = $this->contentNegotiator->negotiateResponseContent(
                TypeResolver::resolveType($rawBody[0]),
                $request
            );
        } else {
            $responseContentNegotiationResult = $this->contentNegotiator->negotiateResponseContent(
                TypeResolver::resolveType($rawBody),
                $request
            );
        }

        $mediaTypeFormatter = $responseContentNegotiationResult->getFormatter();

        if ($mediaTypeFormatter === null) {
            throw new HttpException(HttpStatusCodes::HTTP_NOT_ACCEPTABLE, 'Response content could not be negotiated');
        }

        $bodyStream = new Stream(fopen('php://temp', 'r+b'));

        try {
            $responseContentNegotiationResult->getFormatter()->writeToStream(
                $rawBody,
                $bodyStream,
                $responseContentNegotiationResult->getEncoding()
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
}