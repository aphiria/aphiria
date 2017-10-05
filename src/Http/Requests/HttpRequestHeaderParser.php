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
use Opulence\Net\Collection;
use Opulence\Net\Http\HttpHeaders;

/**
 * Defines the HTTP request header parser
 */
class HttpRequestHeaderParser
{
    /** @var Collection[] The cache of raw cookie headers to parsed collections */
    private $parsedCookieCache = [];

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

    /**
     * Parses the request headers for cookies
     *
     * @param HttpHeaders $headers The headers to parse
     * @return Collection The collection of cookie values
     */
    public function parseCookies(HttpHeaders $headers) : Collection
    {
        if (!$headers->has('Cookie')) {
            return new Collection();
        }

        $rawCookieHeaderValue = $headers->get('Cookie');

        if (isset($this->parsedCookieCache[$rawCookieHeaderValue])) {
            return $this->parsedCookieCache[$rawCookieHeaderValue];
        }

        $explodedCookies = explode('; ', $rawCookieHeaderValue);
        $cookieArray = [];

        foreach ($explodedCookies as $explodedCookie) {
            $explodededNameValuePair = explode('=', $explodedCookie);

            if (count($explodededNameValuePair) !== 2 || $explodededNameValuePair[1] === '') {
                throw new InvalidArgumentException('Cookie must be in the format "name=value"');
            }

            $cookieArray[$explodededNameValuePair[0]] = urldecode($explodededNameValuePair[1]);
        }

        // Cache this for next time
        $cookieCollection = new Collection($cookieArray);
        $this->parsedCookieCache[$rawCookieHeaderValue] = $cookieCollection;

        return $cookieCollection;
    }
}
