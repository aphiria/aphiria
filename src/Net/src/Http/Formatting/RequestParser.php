<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Collections\IDictionary;
use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Net\Formatting\UriParser;
use Aphiria\Net\Http\Headers\AcceptCharsetHeaderValue;
use Aphiria\Net\Http\Headers\AcceptLanguageHeaderValue;
use Aphiria\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\MultipartBody;
use Aphiria\Net\Http\MultipartBodyPart;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the HTTP request message parser
 */
class RequestParser
{
    /** @const The name of the request property that stores the client IP address */
    private const string CLIENT_IP_ADDRESS_PROPERTY = 'CLIENT_IP_ADDRESS';

    /**
     * @param RequestHeaderParser $headerParser The header parser to use
     * @param BodyParser $bodyParser The body parser to use
     * @param UriParser $uriParser The URI parser to use
     */
    public function __construct(
        private readonly RequestHeaderParser $headerParser = new RequestHeaderParser(),
        private readonly BodyParser $bodyParser = new BodyParser(),
        private readonly UriParser $uriParser = new UriParser()
    ) {
    }

    /**
     * Gets the MIME type of the body
     *
     * @param IRequest|MultipartBodyPart $request The request or multipart body part to parse
     * @return string|null The mime type if one is set, otherwise null
     * @throws RuntimeException Thrown if the MIME type could not be determined
     */
    public function getActualMimeType(IRequest|MultipartBodyPart $request): ?string
    {
        return $this->bodyParser->getMimeType($request instanceof IRequest ? $request->body : $request->body);
    }

    /**
     * Gets the client IP address from the request
     *
     * @param IRequest $request The request to look in
     * @return string|null The client IP address if one was found, otherwise null
     */
    public function getClientIPAddress(IRequest $request): ?string
    {
        $clientIPAddress = null;
        $request->properties->tryGet(self::CLIENT_IP_ADDRESS_PROPERTY, $clientIPAddress);

        /** @var string|null $clientIPAddress */
        return $clientIPAddress;
    }

    /**
     * Gets the MIME type that was specified by the client (eg browser)
     *
     * @param MultipartBodyPart $bodyPart The body part whose MIME type we want
     * @return string|null The MIME type if one could be determined, otherwise null
     * @note This may not be the actual MIME type
     */
    public function getClientMimeType(MultipartBodyPart $bodyPart): ?string
    {
        $clientMimeType = null;

        if ($bodyPart->headers->tryGetFirst('Content-Type', $clientMimeType)) {
            return (string)$clientMimeType;
        }

        return null;
    }

    /**
     * Gets whether or not the headers have a JSON content type
     *
     * @param IRequest $request The request to parse
     * @return bool True if the message has a JSON content type, otherwise false
     * @throws RuntimeException Thrown if the content type header's hash key could not be calculated
     */
    public function isJson(IRequest $request): bool
    {
        return $this->headerParser->isJson($request->headers);
    }

    /**
     * Gets whether or not the message is a multipart message
     *
     * @param IRequest $request The request to parse
     * @return bool True if the request is a multipart message, otherwise false
     * @throws RuntimeException Thrown if the content type header's hash key could not be calculated
     */
    public function isMultipart(IRequest $request): bool
    {
        return $this->headerParser->isMultipart($request->headers);
    }

    /**
     * Parses the Accept-Charset header
     *
     * @param IRequest $request The request to parse
     * @return list<AcceptCharsetHeaderValue> The list of charset header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptCharsetHeader(IRequest $request): array
    {
        return $this->headerParser->parseAcceptCharsetHeader($request->headers);
    }

    /**
     * Parses the Accept header
     *
     * @param IRequest $request The request to parse
     * @return list<AcceptMediaTypeHeaderValue> The list of media type header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptHeader(IRequest $request): array
    {
        return $this->headerParser->parseAcceptHeader($request->headers);
    }

    /**
     * Parses the Accept-Language header
     *
     * @param IRequest $request The request to parse
     * @return list<AcceptLanguageHeaderValue> The list of language header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptLanguageHeader(IRequest $request): array
    {
        return $this->headerParser->parseAcceptLanguageHeader($request->headers);
    }

    /**
     * Parses the Content-Type header
     *
     * @param IRequest $request The request to parse
     * @return ContentTypeHeaderValue|null The parsed header if one exists, otherwise null
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseContentTypeHeader(IRequest $request): ?ContentTypeHeaderValue
    {
        return $this->headerParser->parseContentTypeHeader($request->headers);
    }

    /**
     * Parses the request headers for all cookie values
     *
     * @param IRequest $request The request to parse
     * @return IImmutableDictionary<string, string|null> The mapping of cookie names to values
     */
    public function parseCookies(IRequest $request): IImmutableDictionary
    {
        return $this->headerParser->parseCookies($request->headers);
    }

    /**
     * Parses the parameters (semi-colon delimited values for a header) for the first value of a header
     *
     * @param IRequest $request The request to parse
     * @param string $headerName The name of the header whose parameters we're parsing
     * @param int $index The
     * @return IImmutableDictionary<string, string|null> The dictionary of parameters for the first value
     */
    public function parseParameters(
        IRequest $request,
        string $headerName,
        int $index = 0
    ): IImmutableDictionary {
        return $this->headerParser->parseParameters($request->headers, $headerName, $index);
    }

    /**
     * Parses a request's URI's query string into a collection
     *
     * @param IRequest $request The request whose URI we're parsing
     * @return IImmutableDictionary<string, string> The parsed query string
     */
    public function parseQueryString(IRequest $request): IImmutableDictionary
    {
        return $this->uriParser->parseQueryString($request->uri);
    }

    /**
     * Parses a request body as form input
     *
     * @param IRequest $request The request to parse
     * @return IDictionary<string, mixed> The body form input as a collection
     */
    public function readAsFormInput(IRequest $request): IDictionary
    {
        return $this->bodyParser->readAsFormInput($request->body);
    }

    /**
     * Attempts to read the request body as JSON
     *
     * @param IRequest $request The request to parse
     * @return array<mixed, mixed> The request body as JSON
     * @throws RuntimeException Thrown if the body could not be read as JSON
     */
    public function readAsJson(IRequest $request): array
    {
        return $this->bodyParser->readAsJson($request->body);
    }

    /**
     * Parses a request as a multipart request
     *
     * @param IRequest|MultipartBodyPart $request The request or multipart body part to parse
     * @return MultipartBody|null The multipart body if it was set, otherwise null
     * @throws RuntimeException Thrown if the headers' hash keys could not be calculated
     * @note This method should only be called once for best performance
     */
    public function readAsMultipart(IRequest|MultipartBodyPart $request): ?MultipartBody
    {
        $boundary = '';

        if ($request instanceof IRequest) {
            $headers = $request->headers;
            $body = $request->body;
        } else {
            $headers = $request->headers;
            $body = $request->body;
        }

        if (!$this->headerParser->parseParameters($headers, 'Content-Type')->tryGet(
            'boundary',
            $boundary
        )) {
            throw new InvalidArgumentException('"boundary" is missing in Content-Type header');
        }

        /** @var string $boundary */
        return $this->bodyParser->readAsMultipart($body, $boundary);
    }
}
