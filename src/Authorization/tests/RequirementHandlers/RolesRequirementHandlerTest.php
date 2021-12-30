<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests\RequirementHandlers;

use Aphiria\Authorization\AuthorizationContext;
use Aphiria\Authorization\RequirementHandlers\RolesRequirement;
use Aphiria\Authorization\RequirementHandlers\RolesRequirementHandler;
use Aphiria\Security\Claim;
use Aphiria\Security\ClaimType;
use Aphiria\Security\IPrincipal;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RolesRequirementHandlerTest extends TestCase
{
    private RolesRequirementHandler $requirementHandler;

    protected function setUp(): void
    {
        $this->requirementHandler = new RolesRequirementHandler();
    }

    public function getUsersWithMatchingRoles(): array
    {
        $userWithSingleMatchingRole = $this->createMock(IPrincipal::class);
        $userWithSingleMatchingRole->method('getClaims')
            ->with(ClaimType::Role)
            ->willReturn([new Claim(ClaimType::Role, 'admin', 'example.com')]);
        $userWithManyRolesIncludingMatchingOne = $this->createMock(IPrincipal::class);
        $userWithManyRolesIncludingMatchingOne->method('getClaims')
            ->with(ClaimType::Role)
            ->willReturn([
                new Claim(ClaimType::Role, 'admin', 'example.com'),
                new Claim(ClaimType::Role, 'dev', 'example.com')
            ]);

        return [
            [$userWithSingleMatchingRole],
            [$userWithManyRolesIncludingMatchingOne]
        ];
    }

    public function getUsersWithNoMatchingRoles(): array
    {
        $userWithNoRoles = $this->createMock(IPrincipal::class);
        $userWithNoRoles->method('getClaims')
            ->with(ClaimType::Role)
            ->willReturn([]);
        $userWithNoMatchingRoles = $this->createMock(IPrincipal::class);
        $userWithNoMatchingRoles->method('getClaims')
            ->with(ClaimType::Role)
            ->willReturn([new Claim(ClaimType::Role, 'unused', 'example.com')]);

        return [
            [$userWithNoRoles],
            [$userWithNoMatchingRoles]
        ];
    }

    public function testPassingInvalidRequirementTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Requirement must be of type ' . RolesRequirement::class . ', ' . $this::class . ' given');
        $user = $this->createMock(IPrincipal::class);
        $context = new AuthorizationContext($user, [$this], null);
        $this->requirementHandler->handle($user, $this, $context);
    }

    /**
     * @dataProvider getUsersWithMatchingRoles
     *
     * @param IPrincipal $user The user with matching role claims
     */
    public function testUserWithMatchingRoleClaimPasses(IPrincipal $user): void
    {
        $roleRequirement = new RolesRequirement('admin');
        $context = new AuthorizationContext($user, [$roleRequirement], null);
        $this->requirementHandler->handle($user, $roleRequirement, $context);
        $this->assertTrue($context->allRequirementsPassed());
        $this->assertCount(0, $context->pendingRequirements);
    }

    /**
     * @dataProvider getUsersWithNoMatchingRoles
     *
     * @param IPrincipal $user The user with no matching roles
     */
    public function testUserWithNoMatchingRoleClaimsDoesNotPass(IPrincipal $user): void
    {
        $roleRequirement = new RolesRequirement('admin');
        $context = new AuthorizationContext($user, [$roleRequirement], null);
        $this->requirementHandler->handle($user, $roleRequirement, $context);
        $this->assertFalse($context->allRequirementsPassed());
        $this->assertSame([$roleRequirement], $context->pendingRequirements->toArray());
    }
}
