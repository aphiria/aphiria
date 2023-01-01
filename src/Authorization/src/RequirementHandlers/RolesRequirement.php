<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\RequirementHandlers;

/**
 * Defines the required roles requirement
 */
final readonly class RolesRequirement
{
    /** @var list<string> The list of required roles, OR'd together */
    public array $requiredRoles;

    /**
     * @param list<string>|string $requiredRoles The role or list of required roles, OR'd together
     */
    public function __construct(array|string $requiredRoles)
    {
        if (!\is_array($requiredRoles)) {
            $requiredRoles = [$requiredRoles];
        }

        $this->requiredRoles = $requiredRoles;
    }
}
