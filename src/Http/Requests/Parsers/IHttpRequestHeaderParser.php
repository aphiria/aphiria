<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Requests\Parsers;

use Opulence\Net\Http\IHttpHeaders;

/**
 * Defines the interface for HTTP request header parsers to implement
 */
interface IHttpRequestHeaderParser
{
    /**
     * Parses a request header for a cookie value
     *
     * @param IHttpHeaders $headers The headers to parse
     * @param string $name The name of the cookie to get
     * @return mixed The value of the cookie
     */
    public function getCookie(IHttpHeaders $headers, string $name);

    /**
     * Parses a request header for cookies
     *
     * @param IHttpHeaders $headers The headers to parse
     * @return mixed[] The values of the cookies
     */
    public function getCookies(IHttpHeaders $headers) : array;

    /**
     * Gets whether or not the request headers have a JSON content type
     *
     * @param IHttpHeaders $headers The headers to parse
     * @return bool True if the request has a JSON content type, otherwise false
     */
    public function isJson(IHttpHeaders $headers) : bool;

    /**
     * Gets whether or not the request headers have an XHR content type
     *
     * @param IHttpHeaders $headers The headers to parse
     * @return bool True if the request has an XHR content type, otherwise false
     */
    public function isXhr(IHttpHeaders $headers) : bool;
}
