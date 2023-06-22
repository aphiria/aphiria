<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests\Attributes;

use Aphiria\Authorization\Attributes\AuthorizeRoles;
use Aphiria\Authorization\AuthorizationPolicy;
use Aphiria\Authorization\RequirementHandlers\RolesRequirement;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class AuthorizeRolesTest extends TestCase
{
    /**
     * @param list<string|null>|string|null $authenticationSchemeNames The authentication scheme name or list of scheme names, or null if using the default scheme name
     */
    #[TestWith([['foo', 'bar']])]
    #[TestWith([['foo']])]
    #[TestWith([null])]
    public function testAuthenticationSchemeNamesParameterIsAutomaticallySet(array|string|null $authenticationSchemeNames): void
    {
        $attribute = new AuthorizeRoles('admin', $authenticationSchemeNames);
        /** @var AuthorizationPolicy|null $policy */
        $policy = $attribute->parameters['policy'] ?? null;

        if ($authenticationSchemeNames === null) {
            $this->assertNull($policy?->authenticationSchemeNames);
        } else {
            $this->assertSame((array)$authenticationSchemeNames, $policy?->authenticationSchemeNames);
        }
    }

    /**
     * @param list<string>|string $roles The role or list of roles
     */
    #[TestWith([['admin', 'dev']])]
    #[TestWith(['admin'])]
    public function testRolesAreConvertedToRoleRequirements(array|string $roles): void
    {
        $attribute = new AuthorizeRoles($roles);
        /** @var AuthorizationPolicy|null $policy */
        $policy = $attribute->parameters['policy'];
        $this->assertEquals([new RolesRequirement($roles)], $policy?->requirements ?? null);
    }
}
