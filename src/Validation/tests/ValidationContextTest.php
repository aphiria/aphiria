<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests;

use Aphiria\Validation\CircularDependencyException;
use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\ConstraintViolation;
use Aphiria\Validation\ValidationContext;
use PHPUnit\Framework\TestCase;

class ValidationContextTest extends TestCase
{
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
        $this->assertCount(1, $context->constraintViolations);
        $this->assertSame($expectedConstraintViolation, $context->constraintViolations[0]);
    }
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
        $this->assertCount(2, $context->constraintViolations);
        $this->assertSame($expectedConstraintViolation1, $context->constraintViolations[0]);
        $this->assertSame($expectedConstraintViolation2, $context->constraintViolations[1]);
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
        $this->assertCount(3, $context->constraintViolations);
        $this->assertSame($expectedConstraintViolation1, $context->constraintViolations[0]);
        $this->assertSame($expectedConstraintViolation2, $context->constraintViolations[1]);
        $this->assertSame($expectedConstraintViolation3, $context->constraintViolations[2]);
    }

    public function testCircularDependencyDetectedIfObjectAppearsInChildContext(): void
    {
        $object = new class () {
        };
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular dependency on ' . $object::class . ' detected');
        $parentContext = new ValidationContext($object);
        new ValidationContext($object, null, null, $parentContext);
    }

    public function testCircularDependencyIsNotDetectedIfObjectAppearsTwiceInContextChainButOnceWasForMethodValue(): void
    {
        $object = new class () {
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
        $object = new class () {
            public int $prop = 1;
        };
        $parentContext = new ValidationContext($object);
        new ValidationContext($object, 'prop', null, $parentContext);

        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testGettingConstraintViolationsIncludesOnesFromChildren(): void
    {
        $parentContext = new ValidationContext($this);
        $childContext1 = new ValidationContext($this, 'foo', null, $parentContext);
        $childContext2 = new ValidationContext($this, 'bar', null, $parentContext);
        $parentConstraintViolation = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            $this,
            $this
        );
        $parentContext->addConstraintViolation($parentConstraintViolation);
        $childConstraintViolation1 = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'bar',
            $this
        );
        $childConstraintViolation2 = new ConstraintViolation(
            'error',
            $this->createMock(IConstraint::class),
            'baz',
            $this
        );
        $childContext1->addConstraintViolation($childConstraintViolation1);
        $childContext2->addConstraintViolation($childConstraintViolation2);
        $this->assertCount(3, $parentContext->constraintViolations);
        $this->assertSame($parentConstraintViolation, $parentContext->constraintViolations[0]);
        $this->assertSame($childConstraintViolation1, $parentContext->constraintViolations[1]);
        $this->assertSame($childConstraintViolation2, $parentContext->constraintViolations[2]);
    }

    public function testGettingErrorMessagesGetsMessagesFromConstraintViolations(): void
    {
        $constraintViolation1 = new ConstraintViolation('error1', $this->createMock(IConstraint::class), 'foo', $this);
        $constraintViolation2 = new ConstraintViolation('error2', $this->createMock(IConstraint::class), 'bar', $this);
        $context = new ValidationContext($this);
        $context->addManyConstraintViolations([$constraintViolation1, $constraintViolation2]);
        $this->assertEquals(['error1', 'error2'], $context->errorMessages);
    }

    public function testGettingErrorMessagesIncludesMessagesFromChildViolations(): void
    {
        $constraintViolation1 = new ConstraintViolation('error1', $this->createMock(IConstraint::class), 'foo', $this);
        $constraintViolation2 = new ConstraintViolation('error2', $this->createMock(IConstraint::class), 'bar', $this);
        $parentContext = new ValidationContext($this);
        $childContext = new ValidationContext($this, 'prop', null, $parentContext);
        $childContext->addManyConstraintViolations([$constraintViolation1, $constraintViolation2]);
        $this->assertEquals(['error1', 'error2'], $parentContext->errorMessages);
    }

    public function testGettingMethodNameReturnsOneSetInConstructor(): void
    {
        $context = new ValidationContext($this, null, 'method');
        $this->assertSame('method', $context->methodName);
    }

    public function testGettingPropertyNameReturnsOneSetInConstructor(): void
    {
        $context = new ValidationContext($this, 'prop');
        $this->assertSame('prop', $context->propertyName);
    }

    public function testGettingRootValueReturnsParentValueIfParentContextExists(): void
    {
        $parentContext = new ValidationContext($this);
        $childContext = new ValidationContext(new class () {
        }, null, null, $parentContext);
        $this->assertSame($this, $childContext->rootValue);
        $this->assertSame($this, $parentContext->rootValue);
    }

    public function testGettingRootValueReturnsValueIfNoParentContextExists(): void
    {
        $context = new ValidationContext($this);
        $this->assertSame($this, $context->rootValue);
    }

    public function testGettingValueReturnsOneSetInConstructor(): void
    {
        $context = new ValidationContext(1);
        $this->assertSame(1, $context->value);
    }
}
