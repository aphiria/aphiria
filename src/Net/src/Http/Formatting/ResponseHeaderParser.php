<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\Cookie;
use Aphiria\Net\Http\Headers\SameSiteMode;

/**
 * Defines the response header parser
 */
class ResponseHeaderParser extends HeaderParser
{
    /**
     * Parses the response headers for all set cookies
     *
     * @param Headers $headers The headers to parse
     * @return IImmutableDictionary<string, Cookie> The mapping of cookie names to cookies
     */
    public function parseCookies(Headers $headers): IImmutableDictionary
    {
        /** @var list<KeyValuePair<string, Cookie>> $cookieNamesToCookies */
        $cookieNamesToCookies = [];
        $setCookieHeaders = null;

        if (!$headers->tryGet('Set-Cookie', $setCookieHeaders)) {
            return new ImmutableHashTable($cookieNamesToCookies);
        }

        /** @var list<string> $setCookieHeaders */
        $numSetCookieHeaders = \count($setCookieHeaders);

        for ($i = 0;$i < $numSetCookieHeaders;$i++) {
            $name = $cookieValue = $maxAge = $path = $domain = $sameSite = null;
            $isSecure = $isHttpOnly = false;

            /** @var KeyValuePair<string, string> $kvp */
            foreach ($this->parseParameters($headers, 'Set-Cookie', $i) as $key => $value) {
                switch ($key) {
                    case 'Max-Age':
                        $maxAge = (int)$value;
                        break;
                    case 'Path':
                        $path = (string)$value;
                        break;
                    case 'Domain':
                        $domain = (string)$value;
                        break;
                    case 'Secure':
                        $isSecure = true;
                        break;
                    case 'HttpOnly':
                        $isHttpOnly = true;
                        break;
                    case 'SameSite':
                        $sameSite = SameSiteMode::tryFrom((string)$value);
                        break;
                    default:
                        // Treat the default value as the cookie name
                        $name = (string)$key;
                        $cookieValue = $value;
                        break;
                }
            }

            if ($name === null) {
                continue;
            }

            $cookieNamesToCookies[] = new KeyValuePair(
                $name,
                new Cookie(
                    $name,
                    $cookieValue,
                    $maxAge,
                    $path,
                    $domain,
                    $isSecure,
                    $isHttpOnly,
                    $sameSite
                )
            );
        }

        return new ImmutableHashTable($cookieNamesToCookies);
    }
}
