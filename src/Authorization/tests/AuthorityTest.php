<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Authorization\Tests;

use Aphiria\Authorization\Authority;
use Aphiria\Authorization\AuthorizationContext;
use Aphiria\Authorization\AuthorizationPolicy;
use Aphiria\Authorization\AuthorizationPolicyRegistry;
use Aphiria\Authorization\AuthorizationRequirementHandlerRegistry;
use Aphiria\Authorization\IAuthorizationRequirementHandler;
use Aphiria\Authorization\PolicyNotFoundException;
use Aphiria\Authorization\RequirementHandlerNotFoundException;
use Aphiria\Authorization\RequirementHandlers\RolesRequirement;
use Aphiria\Authorization\RequirementHandlers\RolesRequirementHandler;
use Aphiria\Security\Claim;
use Aphiria\Security\ClaimType;
use Aphiria\Security\IPrincipal;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorityTest extends TestCase
{
    private Authority $authority;
    private AuthorizationPolicyRegistry $policies;
    private AuthorizationRequirementHandlerRegistry $requirementHandlers;

    protected function setUp(): void
    {
        $this->policies = new AuthorizationPolicyRegistry();
        $this->requirementHandlers = new AuthorizationRequirementHandlerRegistry();
        // We're going to test not continuing on error by default
        $this->authority = new Authority($this->policies, $this->requirementHandlers, false);
    }

    public function testAuthorizingPolicyNameGetsPolicyByName(): void
    {
        $policy = new AuthorizationPolicy('foo', [new RolesRequirement('admin')], []);
        $this->policies->registerPolicy($policy);
        $this->requirementHandlers->registerRequirementHandler(RolesRequirement::class, new RolesRequirementHandler());
        $user = $this->createMock(IPrincipal::class);
        $user->method('getClaims')
            ->with(ClaimType::Role)
            ->willReturn([new Claim(ClaimType::Role, 'admin', 'example.com')]);
        $result = $this->authority->authorize($user, 'foo');
        $this->assertTrue($result->passed);
    }

    public function testAuthorizingResourcePassesResourceToRequirementHandlers(): void
    {
        $policy = new AuthorizationPolicy('policy', [new RolesRequirement('admin')]);
        $user = $this->createMock(IPrincipal::class);
        /** @var IAuthorizationRequirementHandler<RolesRequirement, AuthorityTest>&MockObject $requirementHandler */
        $requirementHandler = $this->createMock(IAuthorizationRequirementHandler::class);
        $requirementHandler->expects($this->once())
            ->method('handle')
            ->with($user, $policy->requirements[0], $this->callback(fn (AuthorizationContext $context): bool => $context->resource === $this));
        $this->requirementHandlers->registerRequirementHandler(RolesRequirement::class, $requirementHandler);
        $this->authority->authorize($user, $policy, $this);
    }

    public function testAuthorizingUnregisteredPolicyNameThrowsException(): void
    {
        $this->expectException(PolicyNotFoundException::class);
        $this->expectExceptionMessage('No policy with name "policy" found');
        $this->authority->authorize($this->createMock(IPrincipal::class), 'policy');
    }

    public function testAuthorizingUnregisteredRequirementHandlerThrowsException(): void
    {
        $this->expectException(RequirementHandlerNotFoundException::class);
        $this->expectExceptionMessage('No requirement handler for requirement type ' . RolesRequirement::class . ' found');
        $policy = new AuthorizationPolicy('foo', [new RolesRequirement('admin')], []);
        $this->authority->authorize($this->createMock(IPrincipal::class), $policy);
    }

    public function testEachRequirementInPolicyPassingReturnsPassingResult(): void
    {
        $policy = new AuthorizationPolicy(
            'policy',
            [new RolesRequirement('admin'), new RolesRequirement('dev')]
        );
        $this->requirementHandlers->registerRequirementHandler(RolesRequirement::class, new RolesRequirementHandler());
        $user = $this->createMock(IPrincipal::class);
        $user->method('getClaims')
            ->with(ClaimType::Role)
            ->willReturn([
                new Claim(ClaimType::Role, 'admin', 'example.com'),
                new Claim(ClaimType::Role, 'dev', 'example.com')
            ]);
        $result = $this->authority->authorize($user, $policy);
        $this->assertTrue($result->passed);
    }

    public function testFailingSingleRequirementInPolicyReturnsFailingResult(): void
    {
        $policy = new AuthorizationPolicy(
            'policy',
            [new RolesRequirement('admin')],
            []
        );
        $this->requirementHandlers->registerRequirementHandler(RolesRequirement::class, new RolesRequirementHandler());
        $user = $this->createMock(IPrincipal::class);
        $user->method('getClaims')
            ->with(ClaimType::Role)
            ->willReturn([new Claim(ClaimType::Role, 'dev', 'example.com')]);
        $result = $this->authority->authorize($user, $policy);
        $this->assertFalse($result->passed);
        $this->assertSame($policy->requirements, $result->failedRequirements);
    }

    public function testFailingSingleRequirementWithContinueOnErrorOptionContinuesCheckingRequirements(): void
    {
        $authority = new Authority($this->policies, $this->requirementHandlers, true);
        $this->requirementHandlers->registerRequirementHandler(RolesRequirement::class, new RolesRequirementHandler());
        $user = $this->createMock(IPrincipal::class);
        $user->method('getClaims')
            ->with(ClaimType::Role)
            ->willReturn([new Claim(ClaimType::Role, 'dev', 'example.com')]);
        // The first requirement will fail, but not the second one
        $requirements = [new RolesRequirement('admin'), new RolesRequirement('dev')];
        $policy = new AuthorizationPolicy('foo', $requirements, []);
        $result = $authority->authorize($user, $policy);
        $this->assertFalse($result->passed);
        $this->assertSame([$requirements[0]], $result->failedRequirements);
    }
}
