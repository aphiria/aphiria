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
use Aphiria\Authorization\AuthorityBuilder;
use Aphiria\Authorization\AuthorizationPolicy;
use Aphiria\Authorization\AuthorizationPolicyRegistry;
use Aphiria\Authorization\AuthorizationRequirementHandlerRegistry;
use Aphiria\Authorization\RequirementHandlers\RolesRequirement;
use Aphiria\Authorization\RequirementHandlers\RolesRequirementHandler;
use Aphiria\Security\Claim;
use Aphiria\Security\ClaimType;
use Aphiria\Security\IPrincipal;
use PHPUnit\Framework\TestCase;

class AuthorityBuilderTest extends TestCase
{
    private AuthorityBuilder $authorityBuilder;

    protected function setUp(): void
    {
        $this->authorityBuilder = new AuthorityBuilder();
    }

    public function testWithContinueOnErrorSetsContinueOnError(): void
    {
        $authority = $this->authorityBuilder->withContinueOnFailure(true)
            ->withRequirementHandler(RolesRequirement::class, new RolesRequirementHandler())
            ->build();
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

    public function testWithMethodsReturnsSameInstance(): void
    {
        $instance1 = $this->authorityBuilder->withPolicy(new AuthorizationPolicy('policy', $this, []));
        $instance2 = $this->authorityBuilder->withRequirementHandler(RolesRequirement::class, new RolesRequirementHandler());
        $instance3 = $this->authorityBuilder->withContinueOnFailure(true);
        $this->assertSame($instance2, $instance1);
        $this->assertSame($instance3, $instance2);
    }

    public function testWithPolicyCreatesAuthorityWithPolicy(): void
    {
        $policy = new AuthorizationPolicy('policy', $this, []);
        $authority = $this->authorityBuilder->withPolicy($policy)
            ->build();
        $expectedPolicies = new AuthorizationPolicyRegistry();
        $expectedPolicies->registerPolicy($policy);
        $expectedAuthority = new Authority(
            $expectedPolicies,
            new AuthorizationRequirementHandlerRegistry()
        );
        $this->assertEquals($expectedAuthority, $authority);
    }

    public function testWithRequirementHandlerCreatesAuthorityWithRequirementHandler(): void
    {
        $roleRequirementHandler = new RolesRequirementHandler();
        $authority = $this->authorityBuilder->withRequirementHandler(RolesRequirement::class, $roleRequirementHandler)
            ->build();
        $expectedAuthorizationRequirementHandlers = new AuthorizationRequirementHandlerRegistry();
        $expectedAuthorizationRequirementHandlers->registerRequirementHandler(RolesRequirement::class, $roleRequirementHandler);
        $expectedAuthority = new Authority(new AuthorizationPolicyRegistry(), $expectedAuthorizationRequirementHandlers);
        $this->assertEquals($expectedAuthority, $authority);
    }
}
