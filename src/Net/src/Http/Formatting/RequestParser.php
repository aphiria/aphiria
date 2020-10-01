<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
    private const CLIENT_IP_ADDRESS_PROPERTY = 'CLIENT_IP_ADDRESS';
    /** @var RequestHeaderParser The header parser to use */
    private RequestHeaderParser $headerParser;
    /** @var BodyParser The body parser to use */
    private BodyParser $bodyParser;
    /** @var UriParser The URI parser to use */
    private UriParser $uriParser;

    /**
     * @param RequestHeaderParser|null $headerParser The header parser to use, or null if using the default parser
     * @param BodyParser|null $bodyParser The body parser to use, or null if using the default parser
     * @param UriParser|null $uriParser The URI parser to use, or null if using the default parser
     */
    public function __construct(
        RequestHeaderParser $headerParser = null,
        BodyParser $bodyParser = null,
        UriParser $uriParser = null
    ) {
        $this->headerParser = $headerParser ?? new RequestHeaderParser();
        $this->bodyParser = $bodyParser ?? new BodyParser();
        $this->uriParser = $uriParser ?? new UriParser();
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
        return $this->bodyParser->getMimeType($request->getBody());
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
        $request->getProperties()->tryGet(self::CLIENT_IP_ADDRESS_PROPERTY, $clientIPAddress);

        return $clientIPAddress;
    }

    /**
     * Gets the MIME type that was specified by the client (eg browser)
     * Note: This may not be the actual MIME type
     *
     * @param MultipartBodyPart $bodyPart The body part whose MIME type we want
     * @return string|null The MIME type if one could be determined, otherwise null
     */
    public function getClientMimeType(MultipartBodyPart $bodyPart): ?string
    {
        $clientMimeType = null;

        if ($bodyPart->getHeaders()->tryGetFirst('Content-Type', $clientMimeType)) {
            return $clientMimeType;
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
        return $this->headerParser->isJson($request->getHeaders());
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
        return $this->headerParser->isMultipart($request->getHeaders());
    }

    /**
     * Parses the Accept-Charset header
     *
     * @param IRequest $request The request to parse
     * @return AcceptCharsetHeaderValue[] The list of charset header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptCharsetHeader(IRequest $request): array
    {
        return $this->headerParser->parseAcceptCharsetHeader($request->getHeaders());
    }

    /**
     * Parses the Accept header
     *
     * @param IRequest $request The request to parse
     * @return AcceptMediaTypeHeaderValue[] The list of media type header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptHeader(IRequest $request): array
    {
        return $this->headerParser->parseAcceptHeader($request->getHeaders());
    }

    /**
     * Parses the Accept-Language header
     *
     * @param IRequest $request The request to parse
     * @return AcceptLanguageHeaderValue[] The list of language header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptLanguageHeader(IRequest $request): array
    {
        return $this->headerParser->parseAcceptLanguageHeader($request->getHeaders());
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
        return $this->headerParser->parseContentTypeHeader($request->getHeaders());
    }

    /**
     * Parses the request headers for all cookie values
     *
     * @param IRequest $request The request to parse
     * @return IImmutableDictionary The mapping of cookie names to values
     */
    public function parseCookies(IRequest $request): IImmutableDictionary
    {
        return $this->headerParser->parseCookies($request->getHeaders());
    }

    /**
     * Parses the parameters (semi-colon delimited values for a header) for the first value of a header
     *
     * @param IRequest $request The request to parse
     * @param string $headerName The name of the header whose parameters we're parsing
     * @param int $index The
     * @return IImmutableDictionary The dictionary of parameters for the first value
     */
    public function parseParameters(
        IRequest $request,
        string $headerName,
        int $index = 0
    ): IImmutableDictionary {
        return $this->headerParser->parseParameters($request->getHeaders(), $headerName, $index);
    }

    /**
     * Parses a request's URI's query string into a collection
     *
     * @param IRequest $request The request whose URI we're parsing
     * @return IImmutableDictionary The parsed query string
     */
    public function parseQueryString(IRequest $request): IImmutableDictionary
    {
        return $this->uriParser->parseQueryString($request->getUri());
    }

    /**
     * Parses a request body as form input
     *
     * @param IRequest $request The request to parse
     * @return IDictionary The body form input as a collection
     */
    public function readAsFormInput(IRequest $request): IDictionary
    {
        return $this->bodyParser->readAsFormInput($request->getBody());
    }

    /**
     * Attempts to read the request body as JSON
     *
     * @param IRequest $request The request to parse
     * @return array The request body as JSON
     * @throws RuntimeException Thrown if the body could not be read as JSON
     */
    public function readAsJson(IRequest $request): array
    {
        return $this->bodyParser->readAsJson($request->getBody());
    }

    /**
     * Parses a request as a multipart request
     * Note: This method should only be called once for best performance
     *
     * @param IRequest|MultipartBodyPart $request The request or multipart body part to parse
     * @return MultipartBody|null The multipart body if it was set, otherwise null
     * @throws RuntimeException Thrown if the headers' hash keys could not be calculated
     */
    public function readAsMultipart(IRequest|MultipartBodyPart $request): ?MultipartBody
    {
        $boundary = '';

        if (!$this->headerParser->parseParameters($request->getHeaders(), 'Content-Type')->tryGet(
            'boundary',
            $boundary
        )) {
            throw new InvalidArgumentException('"boundary" is missing in Content-Type header');
        }

        return $this->bodyParser->readAsMultipart($request->getBody(), $boundary);
    }
}
