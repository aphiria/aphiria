<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Http\Formatting;

use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\Headers\Cookie;

/**
 * Defines the response header parser
 */
class ResponseHeaderParser extends HeaderParser
{
    /**
     * Parses the response headers for all set cookie
     *
     * @param Headers $headers The headers to parse
     * @return Cookie[] The list of set cookies
     */
    public function parseCookies(Headers $headers): array
    {
        $setCookieHeaders = null;

        if (!$headers->tryGet('Set-Cookie', $setCookieHeaders)) {
            return [];
        }

        $cookies = [];

        /**
         * @var int $i
         * @var string $setCookieHeader
         */
        foreach ($setCookieHeaders as $i => $setCookieHeader) {
            $name = $value = $maxAge = $path = $domain = $sameSite = null;
            $isSecure = $isHttpOnly = false;

            /** @var KeyValuePair $kvp */
            foreach ($this->parseParameters($headers, 'Set-Cookie', $i) as $kvp) {
                switch ($kvp->getKey()) {
                    case 'Max-Age':
                        $maxAge = (int)$kvp->getValue();
                        break;
                    case 'Path':
                        $path = (string)$kvp->getValue();
                        break;
                    case 'Domain':
                        $domain = (string)$kvp->getValue();
                        break;
                    case 'Secure':
                        $isSecure = true;
                        break;
                    case 'HttpOnly':
                        $isHttpOnly = true;
                        break;
                    case 'SameSite':
                        $sameSite = (string)$kvp->getValue();
                        break;
                    default:
                        // Treat the default value as the cookie name
                        $name = (string)$kvp->getKey();
                        /** @psalm-suppress MixedAssignment The value could legitimately be mixed */
                        $value = $kvp->getValue();
                        break;
                }
            }

            if ($name === null) {
                continue;
            }

            $cookies[] = new Cookie(
                $name,
                $value,
                $maxAge,
                $path,
                $domain,
                $isSecure,
                $isHttpOnly,
                $sameSite
            );
        }

        return $cookies;
    }
}
