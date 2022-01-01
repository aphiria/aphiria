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

use Aphiria\Authorization\AuthorizationContext;
use Aphiria\Security\IPrincipal;
use PHPUnit\Framework\TestCase;

class AuthorizationContextTest extends TestCase
{
    public function testAllRequirementsPassedReturnsWhetherOrNotAllRequirementsHavePassed(): void
    {
        $requirements = [
            new class () {
            },
            new class () {
            }
        ];
        $context = $this->createMockContext($requirements);
        $this->assertFalse($context->allRequirementsPassed());
        $context->requirementPassed($requirements[0]);
        $this->assertFalse($context->allRequirementsPassed());
        $context->requirementPassed($requirements[1]);
        $this->assertTrue($context->allRequirementsPassed());
        // Make sure that explicitly marking the context as failed returns false
        $context->fail();
        $this->assertFalse($context->allRequirementsPassed());
    }

    public function testAnyRequirementsFailedReturnsFalseIfAllRequirementsHavePassedSoFarButThereAreStillPendingRequirements(): void
    {
        $requirements = [
            new class () {
            },
            new class () {
            }
        ];
        $context = $this->createMockContext($requirements);
        $this->assertFalse($context->anyRequirementsFailed());
        $context->requirementPassed($requirements[0]);
        $this->assertFalse($context->anyRequirementsFailed());
        $context->requirementPassed($requirements[1]);
    }

    public function testAnyRequirementsFailedReturnsTrueIfAnyRequirementsExplicitlyFailed(): void
    {
        $requirements = [
            new class () {
            },
            new class () {
            }
        ];
        $context = $this->createMockContext($requirements);
        $context->fail();
        $this->assertTrue($context->anyRequirementsFailed());
        $context->requirementPassed($requirements[0]);
        $this->assertTrue($context->anyRequirementsFailed());
        $context->requirementPassed($requirements[1]);
    }

    /**
     * Creates a mock authorization context
     *
     * @param list<object> $requirements The list of requirements
     * @return AuthorizationContext The mocked authorization context
     */
    private function createMockContext(array $requirements): AuthorizationContext
    {
        return new AuthorizationContext($this->createMock(IPrincipal::class), $requirements, null);
    }
}
