<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests\RequirementHandlers;

use Aphiria\Authorization\RequirementHandlers\RolesRequirement;
use PHPUnit\Framework\TestCase;

class RolesRequirementTest extends TestCase
{
    public function getRoles(): array
    {
        return [
            ['admin'],
            [['admin', 'dev']]
        ];
    }

    /**
     * @dataProvider getRoles
     *
     * @param list<string>|string $roles The role or list of roles to test
     */
    public function testRolesAreConvertedToList(array|string $roles): void
    {
        $requirement = new RolesRequirement($roles);
        $this->assertSame((array)$roles, $requirement->requiredRoles);
    }
}
