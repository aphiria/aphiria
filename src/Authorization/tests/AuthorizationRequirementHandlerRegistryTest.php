<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests;

use Aphiria\Authorization\AuthorizationRequirementHandlerRegistry;
use Aphiria\Authorization\RequirementHandlers\RolesRequirement;
use Aphiria\Authorization\RequirementHandlers\RolesRequirementHandler;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class AuthorizationRequirementHandlerRegistryTest extends TestCase
{
    private AuthorizationRequirementHandlerRegistry $requirementHandlers;

    protected function setUp(): void
    {
        $this->requirementHandlers = new AuthorizationRequirementHandlerRegistry();
    }

    public function testGettingRegisteredRequirementHandlerReturnsIt(): void
    {
        $requirementHandler = new RolesRequirementHandler();
        $this->requirementHandlers->registerRequirementHandler(RolesRequirement::class, $requirementHandler);
        $this->assertSame($requirementHandler, $this->requirementHandlers->getRequirementHandler(RolesRequirement::class));
    }

    public function testGettingUnregisteredRequirementHandlerThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('No handler registered for requirement ' . RolesRequirement::class);
        $this->requirementHandlers->getRequirementHandler(RolesRequirement::class);
    }
}
