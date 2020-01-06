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
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\ConstraintViolation;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

/**
 * Tests the validation context
 */
class ValidationContextTest extends TestCase
{
    public function testAddingManyConstraintViolations(): void
    {
        $expectedConstraintViolation1 = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'foo',
            'foo'
        );
        $expectedConstraintViolation2 = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'bar',
            'bar'
        );
        $context = new ValidationContext('foo');
        $context->addManyConstraintViolations([$expectedConstraintViolation1, $expectedConstraintViolation2]);
        $this->assertCount(2, $context->getConstraintViolations());
        $this->assertSame($expectedConstraintViolation1, $context->getConstraintViolations()[0]);
        $this->assertSame($expectedConstraintViolation2, $context->getConstraintViolations()[1]);
    }

    public function testAddingMoreConstraintViolationsAppendsThemToExistingViolations(): void
    {
        $expectedConstraintViolation1 = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'foo',
            'foo'
        );
        $expectedConstraintViolation2 = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'bar',
            'bar'
        );
        $expectedConstraintViolation3 = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'baz',
            'baz'
        );
        $context = new ValidationContext('foo');
        $context->addConstraintViolation($expectedConstraintViolation1);
        $context->addConstraintViolation($expectedConstraintViolation2);
        $context->addManyConstraintViolations([$expectedConstraintViolation3]);
        $this->assertCount(3, $context->getConstraintViolations());
        $this->assertSame($expectedConstraintViolation1, $context->getConstraintViolations()[0]);
        $this->assertSame($expectedConstraintViolation2, $context->getConstraintViolations()[1]);
        $this->assertSame($expectedConstraintViolation3, $context->getConstraintViolations()[2]);
    }

    public function testAddingConstraintViolation(): void
    {
        $expectedConstraintViolation = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'foo',
            'foo'
        );
        $context = new ValidationContext('foo');
        $context->addConstraintViolation($expectedConstraintViolation);
        $this->assertCount(1, $context->getConstraintViolations());
        $this->assertSame($expectedConstraintViolation, $context->getConstraintViolations()[0]);
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

    public function testGettingConstraintViolationsIncludesOnesFromChildren(): void
    {
        $parentContext = new ValidationContext($this);
        $childContext = new ValidationContext($this, 'foo', null, $parentContext);
        $parentConstraintViolation = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            $this,
            $this
        );
        $parentContext->addConstraintViolation($parentConstraintViolation);
        $childConstraintViolation = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'bar',
            $this
        );
        $childContext->addConstraintViolation($childConstraintViolation);
        $this->assertCount(2, $parentContext->getConstraintViolations());
        $this->assertSame($parentConstraintViolation, $parentContext->getConstraintViolations()[0]);
        $this->assertSame($childConstraintViolation, $parentContext->getConstraintViolations()[1]);
    }

    public function testGettingValueReturnsOneSetInConstructor(): void
    {
        $context = new ValidationContext(1);
        $this->assertEquals(1, $context->getValue());
    }
}
