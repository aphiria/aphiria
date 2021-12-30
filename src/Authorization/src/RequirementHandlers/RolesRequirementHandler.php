<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\RequirementHandlers;

use Aphiria\Authorization\AuthorizationContext;
use Aphiria\Authorization\IAuthorizationRequirementHandler;
use Aphiria\Security\Claim;
use Aphiria\Security\ClaimType;
use Aphiria\Security\IPrincipal;
use InvalidArgumentException;

/**
 * Defines the required roles requirement handler
 *
 * @implements IAuthorizationRequirementHandler<RolesRequirement>
 */
final class RolesRequirementHandler implements IAuthorizationRequirementHandler
{
    /**
     * @inheritdoc
     */
    public function handle(IPrincipal $user, object $requirement, AuthorizationContext $authorizationContext): void
    {
        if (!$requirement instanceof RolesRequirement) {
            throw new InvalidArgumentException('Requirement must be of type ' . RolesRequirement::class . ', ' . $requirement::class . ' given');
        }

        $userRoles = \array_map(static fn (Claim $claim): string => (string)$claim->value, $user->getClaims(ClaimType::Role));

        foreach ($requirement->requiredRoles as $requiredRole) {
            if (\in_array($requiredRole, $userRoles, true)) {
                $authorizationContext->requirementPassed($requirement);

                return;
            }
        }

        $authorizationContext->fail();
    }
}
