<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests\RequirementHandlers;

use Aphiria\Authorization\RequirementHandlers\RolesRequirement;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class RolesRequirementTest extends TestCase
{
    /**
     * @param list<string>|string $roles The role or list of roles to test
     */
    #[TestWith(['admin'])]
    #[TestWith([['admin', 'dev']])]
    public function testRolesAreConvertedToList(array|string $roles): void
    {
        $requirement = new RolesRequirement($roles);
        $this->assertSame((array)$roles, $requirement->requiredRoles);
    }
}
