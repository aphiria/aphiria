<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Authorization\Components;

use Aphiria\Authorization\AuthorizationPolicy;
use Aphiria\Authorization\AuthorizationPolicyRegistry;
use Aphiria\Authorization\AuthorizationRequirementHandlerRegistry;
use Aphiria\Authorization\IAuthorizationRequirementHandler;
use Aphiria\Authorization\RequirementHandlers\RolesRequirement;
use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\Authorization\Components\AuthorizationComponent;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorizationComponentTest extends TestCase
{
    private AuthorizationComponent $authorizationComponent;
    private Container $container;
    private AuthorizationPolicyRegistry $policies;
    private AuthorizationRequirementHandlerRegistry $requirementHandlers;

    protected function setUp(): void
    {
        // Using a real container to simplify testing
        $this->container = new Container();
        $this->authorizationComponent = new AuthorizationComponent($this->container);

        $this->container->bindInstance(AuthorizationPolicyRegistry::class, $this->policies = new AuthorizationPolicyRegistry());
        $this->container->bindInstance(AuthorizationRequirementHandlerRegistry::class, $this->requirementHandlers = new AuthorizationRequirementHandlerRegistry());
    }

    public function testBuildRegistersPolicies(): void
    {
        $policy1 = new AuthorizationPolicy('foo', $this);
        $policy2 = new AuthorizationPolicy('bar', $this);
        $this->authorizationComponent->withPolicy($policy1);
        $this->authorizationComponent->withPolicy($policy2);
        $this->authorizationComponent->build();
        $this->assertSame($policy1, $this->policies->getPolicy('foo'));
        $this->assertSame($policy2, $this->policies->getPolicy('bar'));
    }

    public function testBuildRegistersRequirementHandlers(): void
    {
        /** @var IAuthorizationRequirementHandler<RolesRequirement, null>&MockObject $requirementHandler1 */
        $requirementHandler1 = $this->createMock(IAuthorizationRequirementHandler::class);
        /** @var IAuthorizationRequirementHandler<DateTime, null>&MockObject $requirementHandler2 */
        $requirementHandler2 = $this->createMock(IAuthorizationRequirementHandler::class);
        $this->authorizationComponent->withRequirementHandler(RolesRequirement::class, $requirementHandler1);
        $this->authorizationComponent->withRequirementHandler(DateTime::class, $requirementHandler2);
        $this->authorizationComponent->build();
        $this->assertSame($requirementHandler1, $this->requirementHandlers->getRequirementHandler(RolesRequirement::class));
        $this->assertSame($requirementHandler2, $this->requirementHandlers->getRequirementHandler(DateTime::class));
    }
}
