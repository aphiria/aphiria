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
     * Sets a cookie in the headers
     *
     * @param HttpHeaders $headers The headers to set the cookie in
     * @param Cookie $cookie The cookie to set
     */
    public function setCookie(HttpHeaders $headers, Cookie $cookie) : void
    {
        $headers->add('Cookie', $this->getSetCookieHeaderValue($cookie), true);
    }

    /**
     * Sets cookies in the headers
     *
     * @param HttpHeaders $headers The headers to set the cookies in
     * @param Cookie[] $cookies The cookies to set
     */
    public function setCookies(HttpHeaders $headers, array $cookies) : void
    {
        foreach ($cookies as $cookie) {
            $this->setCookie($headers, $cookie);
        }
    }

    /**
     * Gets the set-cookie header value from a cookie
     *
     * @param Cookie $cookie The cookie to serialize
     * @return string The set-cookie header value
     */
    private function getSetCookieHeaderValue(Cookie $cookie) : string
    {
        $headerValue = "{$cookie->getName()}=" . urlencode($cookie->getValue());

        if ($cookie->getExpiration() !== null) {
            $headerValue .= '; Expires=' . $cookie->getExpiration()->format('D, d M Y H:i:s \G\M\T');
        }

        if ($cookie->getMaxAge() !== null) {
            $headerValue .= "; Max-Age={$cookie->getMaxAge()}";
        }

        if ($cookie->getDomain() !== null) {
            $headerValue .= '; Domain=' . urlencode($cookie->getDomain());
        }

        if ($cookie->getPath() !== null) {
            $headerValue .= '; Path=' . urlencode($cookie->getPath());
        }

        if ($cookie->isSecure()) {
            $headerValue .= '; Secure';
        }

        if ($cookie->isHttpOnly()) {
            $headerValue .= '; HttpOnly';
        }

        if ($cookie->getSameSite() !== null) {
            $headerValue .= '; Same-Site=' . urlencode($cookie->getSameSite());
        }

        return $headerValue;
    }
}
