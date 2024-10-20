<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Attributes;

use Aphiria\Authorization\AuthorizationPolicy;
use Aphiria\Authorization\Middleware\Authorize;
use Aphiria\Authorization\RequirementHandlers\RolesRequirement;
use Aphiria\Middleware\Attributes\Middleware;
use Attribute;

/**
 * Defines the attribute used for requiring roles for authorization
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class AuthorizeRoles extends Middleware
{
    /**
     * @param list<string>|string $roles The role or list of roles that will be OR'd together for authorization
     * @param list<string|null>|string|null $authenticationSchemeNames The authentication scheme or schemes to use, or null if using the default one
     */
    public function __construct(
        array|string $roles,
        array|string|null $authenticationSchemeNames = null
    ) {
        $policy = new AuthorizationPolicy('roles', new RolesRequirement($roles), $authenticationSchemeNames);

        parent::__construct(Authorize::class, ['policy' => $policy]);
    }
}
