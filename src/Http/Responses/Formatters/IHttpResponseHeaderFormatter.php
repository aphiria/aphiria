<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Responses\Formatters;

use Opulence\Net\Http\IHttpHeaders;
use Opulence\Net\Http\Responses\Cookie;

/**
 * Defines the interface for response header formatters to implement
 */
interface IHttpResponseHeaderFormatter
{
    /**
     * Deletes a cookie from headers
     *
     * @param IHttpHeaders $headers The headers to format
     * @param string $name The name of the cookie to delete
     * @param string $path The path to the cookie to delete
     * @param null|string $domain The domain of the cookie to delete
     * @param bool $isSecure Whether or not the cookie to be deleted was HTTPS
     * @param bool $isHttpOnly Whether or not the cookie to be deleted was HTTP-only
     */
    public function deleteCookie(
        IHttpHeaders $headers,
        string $name,
        string $path = '/',
        ?string $domain = null,
        bool $isSecure = false,
        bool $isHttpOnly = true
    ) : void;

    /**
     * Gets the cookies from headers
     *
     * @param IHttpHeaders $headers The headers to format
     * @param bool $includeDeletedCookies Whether or not to include deleted cookies
     * @return Cookie[] The list of cookies
     */
    public function getCookies(IHttpHeaders $headers, bool $includeDeletedCookies = false) : array;

    /**
     * Sets a cookie in the headers
     *
     * @param IHttpHeaders $headers The headers to set the cookie in
     * @param Cookie $cookie The cookie to set
     */
    public function setCookie(IHttpHeaders $headers, Cookie $cookie) : void;

    /**
     * Sets cookies in the headers
     *
     * @param IHttpHeaders $headers The headers to set the cookies in
     * @param Cookie[] $cookies The cookies to set
     */
    public function setCookies(IHttpHeaders $headers, array $cookies) : void;
}
