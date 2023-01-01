<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication\Schemes;

use Aphiria\Authentication\AuthenticationSchemeOptions;

/**
 * Defines basic authentication options
 */
final class BasicAuthenticationOptions extends AuthenticationSchemeOptions
{
    /**
     * @inheridoc
     * @param ?string $realm The authentication realm that defines the protection space
     * @link https://datatracker.ietf.org/doc/html/rfc2617#section-1
     */
    public function __construct(public ?string $realm = null, string $claimsIssuer = null)
    {
        parent::__construct($claimsIssuer);
    }
}
