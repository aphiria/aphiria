<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests;

use InvalidArgumentException;
use Opulence\Collections\IDictionary;
use Opulence\Collections\IImmutableDictionary;
use Opulence\Net\Http\HttpBodyParser;
use Opulence\Net\Http\MultipartBody;
use Opulence\Net\Http\MultipartBodyPart;
use Opulence\Net\UriParser;

/**
 * Defines the HTTP request message parser
 */
class RequestParser
{
    /** @const The name of the request property that stores the client IP address */
    private const CLIENT_IP_ADDRESS_PROPERTY = 'CLIENT_IP_ADDRESS';
    /** @var RequestHeaderParser The header parser to use */
    private $headerParser = null;
    /** @var HttpBodyParser The body parser to use */
    private $bodyParser = null;
    /** @var UriParser The URI parser to use */
    private $uriParser = null;

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
        $this->uriParser = $uriParser;
    }

    /**
     * Gets the client IP address from the request
     *
     * @param IHttpRequestMessage $request The request to look in
     * @return string|null The client IP address if one was found, otherwise null
     */
    public function getClientIPAddress(IHttpRequestMessage $request) : ?string
    {
        $clientIPAddress = null;
        $request->getProperties()->tryGet(self::CLIENT_IP_ADDRESS_PROPERTY, $clientIPAddress);

        return $clientIPAddress;
    }

    /**
     * Gets the MIME type of the body
     *
     * @param IHttpRequestMessage|MultipartBodyPart $request The request or multipart body part to parse
     * @return string The mime type
     * @throws InvalidArgumentException Thrown if the request is neither a request nor a multipart body part
     * @throws RuntimeException Thrown if the MIME type could not be determined
     */
    public function getMimeType($request) : string
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
     */
    public function isJson(IHttpRequestMessage $request) : bool
    {
        return $this->headerParser->isJson($request->getHeaders());
    }

    /**
     * Gets whether or not the message is a multipart message
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return bool True if the request is a multipart message, otherwise false
     */
    public function isMultipart(IHttpRequestMessage $request) : bool
    {
        return $this->headerParser->isMultipart($request->getHeaders());
    }

    /**
     * Parses the request headers for all cookie values
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return IImmutableDictionary The mapping of cookie names to values
     */
    public function parseCookies(IHttpRequestMessage $request) : IImmutableDictionary
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
    ) : IImmutableDictionary {
        return $this->headerParser->parseParameters($request->getHeaders(), $headerName, $index);
    }

    /**
     * Parses a request's URI's query string into a collection
     *
     * @param IHttpRequestMessage $request The request whose URI we're parsing
     * @return IImmutableDictionary The parsed query string
     */
    public function parseQueryString(IHttpRequestMessage $request) : IImmutableDictionary
    {
        return $this->uriParser->parseQueryString($request->getUri());
    }

    /**
     * Parses a request body as form input
     *
     * @param IHttpRequestMessage $request The request to parse
     * @return IDictionary The body form input as a collection
     */
    public function readAsFormInput(IHttpRequestMessage $request) : IDictionary
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
    public function readAsJson(IHttpRequestMessage $request) : array
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
     */
    public function readAsMultipart($request) : ?MultipartBody
    {
        if (!$request instanceof IHttpRequestMessage && !$request instanceof MultipartBodyPart) {
            throw new InvalidArgumentException(
                'Request must be of type ' . IHttpRequestMessage::class . ' or ' . MultipartBodyPart::class
            );
        }

        $boundary = null;

        if (!$this->headerParser->parseParameters($request->getHeaders(), 'Content-Type')->tryGet('boundary', $boundary)) {
            throw new InvalidArgumentException('"boundary" is missing in Content-Type header');
        }

        return $this->bodyParser->readAsMultipart($request->getBody(), $boundary);
    }
}
