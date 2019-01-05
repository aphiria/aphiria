<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/net/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use DateTime;
use Opulence\Net\Http\Cookie;
use Opulence\Net\Http\HttpHeaders;
use RuntimeException;

/**
 * Defines the response header formatter
 */
class ResponseHeaderFormatter extends HttpHeaderParser
{
    /** @const The date format to use for the expiration property of cookies */
    private const EXPIRATION_DATE_FORMAT = 'D, d M Y H:i:s \G\M\T';

    /**
     * Deletes a cookie from headers
     *
     * @param HttpHeaders $headers The headers to format
     * @param string $name The name of the cookie to delete
     * @param string|null $path The path to the cookie to delete if set, otherwise null
     * @param string|null $domain The domain of the cookie to delete if set, otherwise null
     * @param bool $isSecure Whether or not the cookie to be deleted was HTTPS
     * @param bool $isHttpOnly Whether or not the cookie to be deleted was HTTP-only
     * @param string|null $sameSite The same-site setting to use if set, otherwise null
     * @throws RuntimeException Thrown if the set cookie header's hash key could not be calculated
     */
    public function deleteCookie(
        HttpHeaders $headers,
        string $name,
        ?string $path = null,
        ?string $domain = null,
        bool $isSecure = false,
        bool $isHttpOnly = true,
        ?string $sameSite = null
    ): void {
        $headerValue = "$name=";
        $expiration = DateTime::createFromFormat('U', 0);
        $headerValue .= "; Expires={$expiration->format(self::EXPIRATION_DATE_FORMAT)}";
        $headerValue .= '; Max-Age=0';

        if ($domain !== null) {
            $headerValue .= '; Domain=' . urlencode($domain);
        }

        if ($path !== null) {
            $headerValue .= '; Path=' . urlencode($path);
        }

        if ($isSecure) {
            $headerValue .= '; Secure';
        }

        if ($isHttpOnly) {
            $headerValue .= '; HttpOnly';
        }

        if ($sameSite !== null) {
            $headerValue .= '; SameSite=' . urlencode($sameSite);
        }

        $headers->add('Set-Cookie', $headerValue, true);
    }

    /**
     * Sets a cookie in the headers
     *
     * @param HttpHeaders $headers The headers to set the cookie in
     * @param Cookie $cookie The cookie to set
     * @throws RuntimeException Thrown if the set cookie header's hash key could not be calculated
     */
    public function setCookie(HttpHeaders $headers, Cookie $cookie): void
    {
        $headers->add('Set-Cookie', $this->getSetCookieHeaderValue($cookie), true);
    }

    /**
     * Sets cookies in the headers
     *
     * @param HttpHeaders $headers The headers to set the cookies in
     * @param Cookie[] $cookies The cookies to set
     * @throws RuntimeException Thrown if the set cookie header's hash key could not be calculated
     */
    public function setCookies(HttpHeaders $headers, array $cookies): void
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
    private function getSetCookieHeaderValue(Cookie $cookie): string
    {
        $headerValue = "{$cookie->getName()}=" . urlencode($cookie->getValue());

        if (($expiration = $cookie->getExpiration()) !== null) {
            $headerValue .= '; Expires=' . $expiration->format(self::EXPIRATION_DATE_FORMAT);
        }

        if (($maxAge = $cookie->getMaxAge()) !== null) {
            $headerValue .= "; Max-Age=$maxAge";
        }

        if (($domain = $cookie->getDomain()) !== null) {
            $headerValue .= '; Domain=' . urlencode($domain);
        }

        if (($path = $cookie->getPath()) !== null) {
            $headerValue .= '; Path=' . urlencode($path);
        }

        if ($cookie->isSecure()) {
            $headerValue .= '; Secure';
        }

        if ($cookie->isHttpOnly()) {
            $headerValue .= '; HttpOnly';
        }

        if (($sameSite = $cookie->getSameSite()) !== null) {
            $headerValue .= '; SameSite=' . urlencode($sameSite);
        }

        return $headerValue;
    }
}
