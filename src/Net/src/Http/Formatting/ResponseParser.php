<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use Aphiria\Net\Http\Headers\Cookie;
use Aphiria\Net\Http\IResponse;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the response parser
 */
class ResponseParser
{
    /**
     * @param ResponseHeaderParser $headerParser The response header parser to use
     */
    public function __construct(private readonly ResponseHeaderParser $headerParser = new ResponseHeaderParser())
    {
    }

    /**
     * Gets whether or not the response headers have a JSON content type
     *
     * @param IResponse $response The response whose headers we want to parse
     * @return bool True if the message has a JSON content type, otherwise false
     * @throws RuntimeException Thrown if the content type header's hash key could not be calculated
     */
    public function isJson(IResponse $response): bool
    {
        return $this->headerParser->isJson($response->getHeaders());
    }

    /**
     * Gets whether or not the message is a multipart message
     *
     * @param IResponse $response The response whose headers we want to parse
     * @return bool True if the request is a multipart message, otherwise false
     * @throws RuntimeException Thrown if the content type header's hash key could not be calculated
     */
    public function isMultipart(IResponse $response): bool
    {
        return $this->headerParser->isMultipart($response->getHeaders());
    }

    /**
     * Parses the Content-Type header from a response
     *
     * @param IResponse $response The response whose headers we want to parse
     * @return ContentTypeHeaderValue|null The parsed header if one exists, otherwise null
     * @throws InvalidArgumentException Thrown if the headers were incorrectly formatted
     */
    public function parseContentTypeHeader(IResponse $response): ?ContentTypeHeaderValue
    {
        return $this->headerParser->parseContentTypeHeader($response->getHeaders());
    }

    /**
     * Parses the response's headers for all set cookies
     *
     * @param IResponse $response The response to parse
     * @return IImmutableDictionary<string, Cookie> The mapping of cookie names to cookies
     */
    public function parseCookies(IResponse $response): IImmutableDictionary
    {
        return $this->headerParser->parseCookies($response->getHeaders());
    }

    /**
     * Parses the parameters (semi-colon delimited values for a header) for the first value of a header in a response
     *
     * @param IResponse $response The response whose headers we want to parse
     * @param string $headerName The name of the header whose parameters we're parsing
     * @param int $index The index of the header value to parse
     * @return IImmutableDictionary<string, string|null> The dictionary of parameters for the first value
     */
    public function parseParameters(IResponse $response, string $headerName, int $index = 0): IImmutableDictionary
    {
        return $this->headerParser->parseParameters($response->getHeaders(), $headerName, $index);
    }
}
