<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authentication;

/**
 * Defines the options for an authentication scheme
 */
class AuthenticationSchemeOptions
{
    /**
     * @param string|null $claimsIssuer The claims issuer for this scheme
     */
    public function __construct(public ?string $claimsIssuer = null)
    {
    }
}
