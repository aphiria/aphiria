<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Net\Formatting\UriParser;
use Aphiria\Net\Http\Headers\AcceptCharsetHeaderValue;
use Aphiria\Net\Http\Headers\AcceptLanguageHeaderValue;
use Aphiria\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\MultipartBody;
use Aphiria\Net\Http\MultipartBodyPart;
use InvalidArgumentException;
use Opulence\Collections\IDictionary;
use Opulence\Collections\IImmutableDictionary;
use RuntimeException;

/**
 * Defines the HTTP request message parser
 */
class RequestParser
{
    /** @const The name of the request property that stores the client IP address */
    private const CLIENT_IP_ADDRESS_PROPERTY = 'CLIENT_IP_ADDRESS';
    /** @var RequestHeaderParser The header parser to use */
    private $headerParser;
    /** @var HttpBodyParser The body parser to use */
    private $bodyParser;
    /** @var UriParser The URI parser to use */
    private $uriParser;

    /**
     * @param RequestHeaderParser|null $headerParser The header parser to use, or null if using the default parser
     * @param HttpBodyParser|null $bodyParser The body parser to use, or null if using the default parser
     * @param UriParser|null $uriParser The URI parser to use, or null if using the default parser
     */
    public function __construct(
        RequestHeaderParser $headerParser = null,
        HttpBodyParser $bodyParser = null,
        UriParser $uriParser = null
    ) {
        $this->headerParser = $headerParser ?? new RequestHeaderParser();
        $this->bodyParser = $bodyParser ?? new HttpBodyParser();
        $this->uriParser = $uriParser ?? new UriParser();
    }

    /**
     * Gets the client IP address from the request
     *
     * @param IHttpRequestMessage $request The request to look in
     * @return string|null The client IP address if one was found, otherwise null
     */
    public function getClientIPAddress(IHttpRequestMessage $request): ?string
    {
        $clientIPAddress = null;
        $request->getProperties()->tryGet(self::CLIENT_IP_ADDRESS_PROPERTY, $clientIPAddress);

        return $clientIPAddress;
    }

    /**
     * Gets the MIME type of the body
     *
     * @param IHttpRequestMessage|MultipartBodyPart $request The request or multipart body part to parse
     * @return string|null The mime type if one is set, otherwise null
     * @throws InvalidArgumentException Thrown if the request is neither a request nor a multipart body part
     * @throws RuntimeException Thrown if the MIME type could not be determined
     */
    public function getMimeType($request): ?string
    {
        if (!$request instanceof IHttpRequestMessage && !$request instanceof MultipartBodyPart) {
            throw new InvalidArgumentException(
                'Request must be of type ' . IHttpRequestMessage::class . ' or ' . MultipartBodyPart::class
            );
        }

        return $this->bodyParser->getMimeType($request->getBody());
    }

    /**
     * Gets whether or not the headers have a JSON content type
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return bool True if the message has a JSON content type, otherwise false
     * @throws RuntimeException Thrown if the content type header's hash key could not be calculated
     */
    public function isJson(IHttpRequestMessage $request): bool
    {
        return $this->headerParser->isJson($request->getHeaders());
    }

    /**
     * Gets whether or not the message is a multipart message
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return bool True if the request is a multipart message, otherwise false
     * @throws RuntimeException Thrown if the content type header's hash key could not be calculated
     */
    public function isMultipart(IHttpRequestMessage $request): bool
    {
        return $this->headerParser->isMultipart($request->getHeaders());
    }

    /**
     * Parses the Accept-Charset header
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return AcceptCharsetHeaderValue[] The list of charset header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptCharsetHeader(IHttpRequestMessage $request): array
    {
        return $this->headerParser->parseAcceptCharsetHeader($request->getHeaders());
    }

    /**
     * Parses the Accept header
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return AcceptMediaTypeHeaderValue[] The list of media type header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptHeader(IHttpRequestMessage $request): array
    {
        return $this->headerParser->parseAcceptHeader($request->getHeaders());
    }

    /**
     * Parses the Accept-Language header
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return AcceptLanguageHeaderValue[] The list of language header values
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseAcceptLanguageHeader(IHttpRequestMessage $request): array
    {
        return $this->headerParser->parseAcceptLanguageHeader($request->getHeaders());
    }

    /**
     * Parses the Content-Type header
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return ContentTypeHeaderValue|null The parsed header if one exists, otherwise null
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseContentTypeHeader(IHttpRequestMessage $request): ?ContentTypeHeaderValue
    {
        return $this->headerParser->parseContentTypeHeader($request->getHeaders());
    }

    /**
     * Parses the request headers for all cookie values
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return IImmutableDictionary The mapping of cookie names to values
     */
    public function parseCookies(IHttpRequestMessage $request): IImmutableDictionary
    {
        return $this->headerParser->parseCookies($request->getHeaders());
    }

    /**
     * Parses the parameters (semi-colon delimited values for a header) for the first value of a header
     *
     * @param IHttpRequestMessage $request The request to parse
     * @param string $headerName The name of the header whose parameters we're parsing
     * @param int $index The
     * @return IImmutableDictionary The dictionary of parameters for the first value
     */
    public function parseParameters(
        IHttpRequestMessage $request,
        string $headerName,
        int $index = 0
    ): IImmutableDictionary {
        return $this->headerParser->parseParameters($request->getHeaders(), $headerName, $index);
    }

    /**
     * Parses a request's URI's query string into a collection
     *
     * @param IHttpRequestMessage $request The request whose URI we're parsing
     * @return IImmutableDictionary The parsed query string
     */
    public function parseQueryString(IHttpRequestMessage $request): IImmutableDictionary
    {
        return $this->uriParser->parseQueryString($request->getUri());
    }

    /**
     * Parses a request body as form input
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return IDictionary The body form input as a collection
     */
    public function readAsFormInput(IHttpRequestMessage $request): IDictionary
    {
        return $this->bodyParser->readAsFormInput($request->getBody());
    }

    /**
     * Attempts to read the request body as JSON
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return array The request body as JSON
     * @throws RuntimeException Thrown if the body could not be read as JSON
     */
    public function readAsJson(IHttpRequestMessage $request): array
    {
        return $this->bodyParser->readAsJson($request->getBody());
    }

    /**
     * Parses a request as a multipart request
     * Note: This method should only be called once for best performance
     *
     * @param IHttpRequestMessage|MultipartBodyPart $request The request or multipart body part to parse
     * @return MultipartBody|null The multipart body if it was set, otherwise null
     * @throws InvalidArgumentException Thrown if the request is not a multipart request
     * @throws RuntimeException Thrown if the headers' hash keys could not be calculated
     */
    public function readAsMultipart($request): ?MultipartBody
    {
        if (!$request instanceof IHttpRequestMessage && !$request instanceof MultipartBodyPart) {
            throw new InvalidArgumentException(
                'Request must be of type ' . IHttpRequestMessage::class . ' or ' . MultipartBodyPart::class
            );
        }

        $boundary = null;

        if (!$this->headerParser->parseParameters($request->getHeaders(), 'Content-Type')->tryGet(
            'boundary',
            $boundary
        )) {
            throw new InvalidArgumentException('"boundary" is missing in Content-Type header');
        }

        return $this->bodyParser->readAsMultipart($request->getBody(), $boundary);
    }
}
