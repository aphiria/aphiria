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
use Opulence\Net\Http\HttpHeaders;

/**
 * Defines the HTTP request header parser
 */
class HttpRequestHeaderParser
{
    /** @var The cache of raw cookie headers to name => value pairs */
    private $parsedCookieCache = [];

    /**
     * Parses a request header for cookies
     *
     * @param HttpHeaders $headers The headers to parse
     * @return mixed[] The values of the cookies
     * @throws InvalidArgumentException Thrown if a cookie is not in the correct format
     */
    public function getAllCookies(HttpHeaders $headers) : array
    {
        if (!$headers->has('Cookie')) {
            return [];
        }

        $rawCookieHeaderValue = $headers->get('Cookie');

        if (isset($this->parsedCookieCache[$rawCookieHeaderValue])) {
            return $this->parsedCookieCache[$rawCookieHeaderValue];
        }

        $explodedCookies = explode('; ', $rawCookieHeaderValue);
        $allCookies = [];

        foreach ($explodedCookies as $explodedCookie) {
            $explodededNameValuePair = explode('=', $explodedCookie);

            if (count($explodededNameValuePair) !== 2 || $explodededNameValuePair[1] === '') {
                throw new InvalidArgumentException('Cookie must be in the format "name=value"');
            }

            $allCookies[$explodededNameValuePair[0]] = urldecode($explodededNameValuePair[1]);
        }

        // Cache this for next time
        $this->parsedCookieCache[$rawCookieHeaderValue] = $allCookies;

        return $allCookies;
    }

    /**
     * Parses a request header for a cookie value
     *
     * @param HttpHeaders $headers The headers to parse
     * @param string $name The name of the cookie to get
     * @return mixed|null The value of the cookie if it exists, otherwise null
     * @return InvalidArgumentException Thrown if the cookie is not in the correct format
     */
    public function getCookieValue(HttpHeaders $headers, string $name)
    {
        if (!$headers->has('Cookie')) {
            return null;
        }

        return $this->getAllCookies($headers)[$name] ?? null;
    }

    /**
     * Gets whether or not the request headers have a JSON content type
     *
     * @param HttpHeaders $headers The headers to parse
     * @return bool True if the request has a JSON content type, otherwise false
     */
    public function isJson(HttpHeaders $headers) : bool
    {
        return preg_match("/application\/json/i", $headers->get('Content-Type')) === 1;
    }

    /**
     * Gets whether or not the request headers have an XHR content type
     *
     * @param HttpHeaders $headers The headers to parse
     * @return bool True if the request has an XHR content type, otherwise false
     */
    public function isXhr(HttpHeaders $headers) : bool
    {
        return $headers->get('X-Requested-With') === 'XMLHttpRequest';
    }
}
