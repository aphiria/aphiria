<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Schemes;

use Aphiria\Authentication\AuthenticationSchemeOptions;
use Aphiria\Net\Http\Headers\SameSiteMode;

/**
 * Defines options for cookie authentication
 */
final class CookieAuthenticationOptions extends AuthenticationSchemeOptions
{
    /**
     * @inheritdoc
     * @param string $cookieName The name of the cookie
     * @param int|null $cookieMaxAge The expiration (in seconds from now) the cookie is valid for, or null if it's a session cookie
     * @param string|null $cookiePath The path the cookie applies to
     * @param string|null $cookieDomain The domain the cookie applies to
     * @param bool $cookieIsSecure Whether or not this cookie is HTTPS-only
     * @param bool $cookieIsHttpOnly Whether or not this cookie can be read client-side
     * @param SameSiteMode|null $cookieSameSite The same-site setting to use (defaults to lax), or null if none is specified
     */
    public function __construct(
        public readonly string $cookieName,
        public readonly ?int $cookieMaxAge = null,
        public readonly ?string $cookiePath = null,
        public readonly ?string $cookieDomain = null,
        public readonly bool $cookieIsSecure = false,
        public readonly bool $cookieIsHttpOnly = true,
        public readonly ?SameSiteMode $cookieSameSite = SameSiteMode::Lax,
        public readonly ?string $loginPagePath = null,
        public readonly ?string $forbiddenPagePath = null,
        string $claimsIssuer = null
    ) {
        parent::__construct($claimsIssuer);
    }
}
