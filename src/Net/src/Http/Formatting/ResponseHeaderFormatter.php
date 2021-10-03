<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\Cookie;
use RuntimeException;

/**
 * Defines the response header formatter
 */
class ResponseHeaderFormatter extends HeaderParser
{
    /**
     * Deletes a cookie from headers
     *
     * @param Headers $headers The headers to format
     * @param string $name The name of the cookie to delete
     * @param string|null $path The path to the cookie to delete if set, otherwise null
     * @param string|null $domain The domain of the cookie to delete if set, otherwise null
     * @param bool $isSecure Whether or not the cookie to be deleted was HTTPS
     * @param bool $isHttpOnly Whether or not the cookie to be deleted was HTTP-only
     * @param string|null $sameSite The same-site setting to use if set, otherwise null
     * @throws RuntimeException Thrown if the set cookie header's hash key could not be calculated
     */
    public function deleteCookie(
        Headers $headers,
        string $name,
        ?string $path = null,
        ?string $domain = null,
        bool $isSecure = false,
        bool $isHttpOnly = true,
        ?string $sameSite = Cookie::SAME_SITE_LAX
    ): void {
        $headerValue = "$name=";
        $headerValue .= '; Max-Age=0';

        if ($path !== null) {
            $headerValue .= "; Path=$path";
        }

        if ($domain !== null) {
            $headerValue .= "; Domain=$domain";
        }

        if ($isSecure) {
            $headerValue .= '; Secure';
        }

        if ($isHttpOnly) {
            $headerValue .= '; HttpOnly';
        }

        if ($sameSite !== null) {
            $headerValue .= "; SameSite=$sameSite";
        }

        $headers->add('Set-Cookie', $headerValue, true);
    }

    /**
     * Sets a cookie in the headers
     *
     * @param Headers $headers The headers to set the cookie in
     * @param Cookie $cookie The cookie to set
     * @throws RuntimeException Thrown if the set cookie header's hash key could not be calculated
     */
    public function setCookie(Headers $headers, Cookie $cookie): void
    {
        $headers->add('Set-Cookie', $this->getSetCookieHeaderValue($cookie), true);
    }

    /**
     * Sets cookies in the headers
     *
     * @param Headers $headers The headers to set the cookies in
     * @param list<Cookie> $cookies The cookies to set
     * @throws RuntimeException Thrown if the set cookie header's hash key could not be calculated
     */
    public function setCookies(Headers $headers, array $cookies): void
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
        $headerValue = "{$cookie->getName()}=" . \urlencode((string)$cookie->value);

        if (($maxAge = $cookie->maxAge) !== null) {
            $headerValue .= "; Max-Age=$maxAge";
        }

        if (($path = $cookie->path) !== null) {
            $headerValue .= "; Path=$path";
        }

        if (($domain = $cookie->domain) !== null) {
            $headerValue .= "; Domain=$domain";
        }

        if ($cookie->isSecure) {
            $headerValue .= '; Secure';
        }

        if ($cookie->isHttpOnly) {
            $headerValue .= '; HttpOnly';
        }

        if (($sameSite = $cookie->getSameSite()) !== null) {
            $headerValue .= "; SameSite=$sameSite";
        }

        return $headerValue;
    }
}
