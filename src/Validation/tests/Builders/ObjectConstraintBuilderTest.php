<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Builders;

use Aphiria\Validation\Builders\ObjectConstraintBuilder;
use Aphiria\Validation\Constraints\IConstraint;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the object constraint builder
 */
class ObjectConstraintBuilderTest extends TestCase
{
    private const CLASS_NAME = 'foo';
    private ObjectConstraintBuilder $constraintBuilder;

    protected function setUp(): void
    {
        $this->constraintBuilder = new ObjectConstraintBuilder(self::CLASS_NAME);
    }

    public function testAddingConstraintsToMethodThenToAnotherMethodAddsThemToCorrectField(): void
    {
        $expectedMethod1Constraint = $this->createMock(IConstraint::class);
        $expectedMethod2Constraint = $this->createMock(IConstraint::class);
        $this->constraintBuilder->hasMethod('method1')
            ->withConstraint($expectedMethod1Constraint)
            ->hasMethod('method2')
            ->withConstraint($expectedMethod2Constraint);
        $objectConstraints = $this->constraintBuilder->build();
        $this->assertSame([$expectedMethod1Constraint], $objectConstraints->getMethodConstraints('method1'));
        $this->assertSame([$expectedMethod2Constraint], $objectConstraints->getMethodConstraints('method2'));
    }

    public function testAddingConstraintsToMethodThenToPropertyAddsThemToCorrectField(): void
    {
        $expectedMethodConstraint = $this->createMock(IConstraint::class);
        $expectedPropertyConstraint = $this->createMock(IConstraint::class);
        $this->constraintBuilder->hasMethod('method')
            ->withConstraint($expectedMethodConstraint)
            ->hasProperty('prop')
            ->withConstraint($expectedPropertyConstraint);
        $objectConstraints = $this->constraintBuilder->build();
        $this->assertSame([$expectedMethodConstraint], $objectConstraints->getMethodConstraints('method'));
        $this->assertSame([$expectedPropertyConstraint], $objectConstraints->getPropertyConstraints('prop'));
    }

    public function testAddingConstraintsToPropertyThenToAnotherPropertyAddsThemToCorrectField(): void
    {
        $expectedProperty1Constraint = $this->createMock(IConstraint::class);
        $expectedProperty2Constraint = $this->createMock(IConstraint::class);
        $this->constraintBuilder->hasProperty('prop1')
            ->withConstraint($expectedProperty1Constraint)
            ->hasProperty('prop2')
            ->withConstraint($expectedProperty2Constraint);
        $objectConstraints = $this->constraintBuilder->build();
        $this->assertSame([$expectedProperty1Constraint], $objectConstraints->getPropertyConstraints('prop1'));
        $this->assertSame([$expectedProperty2Constraint], $objectConstraints->getPropertyConstraints('prop2'));
    }

    public function testAddingConstraintsToPropertyThenToMethodAddsThemToCorrectField(): void
    {
        $expectedMethodConstraint = $this->createMock(IConstraint::class);
        $expectedPropertyConstraint = $this->createMock(IConstraint::class);
        $this->constraintBuilder->hasProperty('prop')
            ->withConstraint($expectedPropertyConstraint)
            ->hasMethod('method')
            ->withConstraint($expectedMethodConstraint);
        $objectConstraints = $this->constraintBuilder->build();
        $this->assertSame([$expectedMethodConstraint], $objectConstraints->getMethodConstraints('method'));
        $this->assertSame([$expectedPropertyConstraint], $objectConstraints->getPropertyConstraints('prop'));
    }

    public function testWithConstraintForMethodAddsConstraintToMethod(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $this->constraintBuilder->hasMethod('method')
            ->withConstraint($expectedConstraint);
        $objectConstraints = $this->constraintBuilder->build();
        $this->assertEquals([$expectedConstraint], $objectConstraints->getMethodConstraints('method'));
    }

    public function testWithConstraintForPropertyAddsConstraintToProperty(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $this->constraintBuilder->hasProperty('prop')
            ->withConstraint($expectedConstraint);
        $objectConstraints = $this->constraintBuilder->build();
        $this->assertEquals([$expectedConstraint], $objectConstraints->getPropertyConstraints('prop'));
    }

    public function testWithConstraintWithoutSettingFieldThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Must call hasMethod() or hasProperty() before adding constraints');
        $this->constraintBuilder->withManyConstraints([$this->createMock(IConstraint::class)]);
    }

    public function testWithManyConstraintsForMethodAddsConstraintsToMethod(): void
    {
        $expectedConstraints = [
            $this->createMock(IConstraint::class),
            $this->createMock(IConstraint::class)
        ];
        $this->constraintBuilder->hasMethod('method')
            ->withManyConstraints($expectedConstraints);
        $objectConstraints = $this->constraintBuilder->build();
        $this->assertEquals($expectedConstraints, $objectConstraints->getMethodConstraints('method'));
    }

    public function testWithManyConstraintsForPropertyAddsConstraintsToProperty(): void
    {
        $expectedConstraints = [
            $this->createMock(IConstraint::class),
            $this->createMock(IConstraint::class)
        ];
        $this->constraintBuilder->hasProperty('prop')
            ->withManyConstraints($expectedConstraints);
        $objectConstraints = $this->constraintBuilder->build();
        $this->assertEquals($expectedConstraints, $objectConstraints->getPropertyConstraints('prop'));
    }

    public function testWithManyConstraintsWithoutSettingFieldThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Must call hasMethod() or hasProperty() before adding constraints');
        $this->constraintBuilder->withConstraint($this->createMock(IConstraint::class));
    }
}
