<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests;

use Aphiria\Validation\CircularDependencyException;
use Aphiria\Validation\Rules\IRule;
use Aphiria\Validation\RuleViolation;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validation context
 */
class ValidationContextTest extends TestCase
{
    public function testAddingManyRuleViolations(): void
    {
        $expectedRuleViolation1 = new RuleViolation(
            $this->createMock(IRule::class),
            'foo',
            'foo'
        );
        $expectedRuleViolation2 = new RuleViolation(
            $this->createMock(IRule::class),
            'bar',
            'bar'
        );
        $context = new ValidationContext('foo');
        $context->addManyRuleViolations([$expectedRuleViolation1, $expectedRuleViolation2]);
        $this->assertCount(2, $context->getRuleViolations());
        $this->assertSame($expectedRuleViolation1, $context->getRuleViolations()[0]);
        $this->assertSame($expectedRuleViolation2, $context->getRuleViolations()[1]);
    }

    public function testAddingMoreRuleViolationsAppendsThemToExistingViolations(): void
    {
        $expectedRuleViolation1 = new RuleViolation(
            $this->createMock(IRule::class),
            'foo',
            'foo'
        );
        $expectedRuleViolation2 = new RuleViolation(
            $this->createMock(IRule::class),
            'bar',
            'bar'
        );
        $expectedRuleViolation3 = new RuleViolation(
            $this->createMock(IRule::class),
            'baz',
            'baz'
        );
        $context = new ValidationContext('foo');
        $context->addRuleViolation($expectedRuleViolation1);
        $context->addRuleViolation($expectedRuleViolation2);
        $context->addManyRuleViolations([$expectedRuleViolation3]);
        $this->assertCount(3, $context->getRuleViolations());
        $this->assertSame($expectedRuleViolation1, $context->getRuleViolations()[0]);
        $this->assertSame($expectedRuleViolation2, $context->getRuleViolations()[1]);
        $this->assertSame($expectedRuleViolation3, $context->getRuleViolations()[2]);
    }

    public function testAddingRuleViolation(): void
    {
        $expectedRuleViolation = new RuleViolation(
            $this->createMock(IRule::class),
            'foo',
            'foo'
        );
        $context = new ValidationContext('foo');
        $context->addRuleViolation($expectedRuleViolation);
        $this->assertCount(1, $context->getRuleViolations());
        $this->assertSame($expectedRuleViolation, $context->getRuleViolations()[0]);
    }

    public function testCircularDependencyDetectedIfObjectAppearsInChildContext(): void
    {
        $object = new class() {};
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency on ' . \get_class($object) . ' detected');
        $parentContext = new ValidationContext($object);
        new ValidationContext($object, null, null, $parentContext);
    }

    public function testCircularDependencyIsNotDetectedIfObjectAppearsTwiceInContextChainButOnceWasForMethodValue(): void
    {
        $object = new class()
        {
            public function method(): int
            {
                return 1;
            }
        };
        $parentContext = new ValidationContext($object);
        new ValidationContext($object, null, 'method', $parentContext);

        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testCircularDependencyIsNotDetectedIfObjectAppearsTwiceInContextChainButOnceWasForPropertyValue(): void
    {
        $object = new class()
        {
            public int $prop = 1;
        };
        $parentContext = new ValidationContext($object);
        new ValidationContext($object, 'prop', null, $parentContext);

        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testGettingMethodNameReturnsOneSetInConstructor(): void
    {
        $context = new ValidationContext($this, null, 'method');
        $this->assertEquals('method', $context->getMethodName());
    }

    public function testGettingPropertyNameReturnsOneSetInConstructor(): void
    {
        $context = new ValidationContext($this, 'prop');
        $this->assertEquals('prop', $context->getPropertyName());
    }

    public function testGettingRootValueReturnsParentValueIfParentContextExists(): void
    {
        $parentContext = new ValidationContext($this);
        $childContext = new ValidationContext(new class {}, null, null, $parentContext);
        $this->assertSame($this, $childContext->getRootValue());
        $this->assertSame($this, $parentContext->getRootValue());
    }

    public function testGettingRootValueReturnsValueIfNoParentContextExists(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this, $context->getRootValue());
    }

    public function testGettingRuleViolationsIncludesOnesFromChildren(): void
    {
        $parentContext = new ValidationContext($this);
        $childContext = new ValidationContext($this, 'foo', null, $parentContext);
        $parentRuleViolation = new RuleViolation(
            $this->createMock(IRule::class),
            $this,
            $this
        );
        $parentContext->addRuleViolation($parentRuleViolation);
        $childRuleViolation = new RuleViolation(
            $this->createMock(IRule::class),
            'bar',
            $this
        );
        $childContext->addRuleViolation($childRuleViolation);
        $this->assertCount(2, $parentContext->getRuleViolations());
        $this->assertSame($parentRuleViolation, $parentContext->getRuleViolations()[0]);
        $this->assertSame($childRuleViolation, $parentContext->getRuleViolations()[1]);
    }

    public function testGettingValueReturnsOneSetInConstructor(): void
    {
        $context = new ValidationContext(1);
        $this->assertEquals(1, $context->getValue());
    }
}
