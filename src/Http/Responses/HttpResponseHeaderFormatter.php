<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses;

use Opulence\Net\Http\HttpHeaders;

/**
 * Defines the response header formatter
 */
class HttpResponseHeaderFormatter
{
    /**
     * Deletes a cookie from headers
     *
     * @param HttpHeaders $headers The headers to format
     * @param string $name The name of the cookie to delete
     * @param string $path The path to the cookie to delete
     * @param null|string $domain The domain of the cookie to delete
     * @param bool $isSecure Whether or not the cookie to be deleted was HTTPS
     * @param bool $isHttpOnly Whether or not the cookie to be deleted was HTTP-only
     */
    public function deleteCookie(
        HttpHeaders $headers,
        string $name,
        string $path = '/',
        ?string $domain = null,
        bool $isSecure = false,
        bool $isHttpOnly = true
    ) : void {
        // Todo
    }

    /**
     * Gets the cookies from headers
     *
     * @param HttpHeaders $headers The headers to format
     * @param bool $includeDeletedCookies Whether or not to include deleted cookies
     * @return Cookie[] The list of cookies
     */
    public function getCookies(HttpHeaders $headers, bool $includeDeletedCookies = false) : array
    {
        // Todo
    }

    /**
     * Sets a cookie in the headers
     *
     * @param HttpHeaders $headers The headers to set the cookie in
     * @param Cookie $cookie The cookie to set
     */
    public function setCookie(HttpHeaders $headers, Cookie $cookie) : void
    {
        // Todo
    }

    /**
     * Sets cookies in the headers
     *
     * @param HttpHeaders $headers The headers to set the cookies in
     * @param Cookie[] $cookies The cookies to set
     */
    public function setCookies(HttpHeaders $headers, array $cookies) : void
    {
        // Todo
    }
}
