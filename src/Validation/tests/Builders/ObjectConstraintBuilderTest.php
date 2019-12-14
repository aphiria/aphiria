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
use Aphiria\Validation\ConstraintRegistry;
use Aphiria\Validation\Constraints\IValidationConstraint;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the object constraint builder
 */
class ObjectConstraintBuilderTest extends TestCase
{
    private const CLASS_NAME = 'foo';
    private ObjectConstraintBuilder $constraintBuilder;
    private ConstraintRegistry $constraints;

    protected function setUp(): void
    {
        $this->constraints = new ConstraintRegistry();
        $this->constraintBuilder = new ObjectConstraintBuilder(self::CLASS_NAME, $this->constraints);
    }

    public function testAddingConstraintsToMethodThenToAnotherMethodAddsThemToCorrectField(): void
    {
        $expectedMethod1Constraint = $this->createMock(IValidationConstraint::class);
        $expectedMethod2Constraint = $this->createMock(IValidationConstraint::class);
        $this->constraintBuilder->hasMethod('method1')
            ->withConstraint($expectedMethod1Constraint)
            ->hasMethod('method2')
            ->withConstraint($expectedMethod2Constraint);
        $this->assertSame([$expectedMethod1Constraint], $this->constraints->getMethodConstraints(self::CLASS_NAME, 'method1'));
        $this->assertSame([$expectedMethod2Constraint], $this->constraints->getMethodConstraints(self::CLASS_NAME, 'method2'));
    }

    public function testAddingConstraintsToMethodThenToPropertyAddsThemToCorrectField(): void
    {
        $expectedMethodConstraint = $this->createMock(IValidationConstraint::class);
        $expectedPropertyConstraint = $this->createMock(IValidationConstraint::class);
        $this->constraintBuilder->hasMethod('method')
            ->withConstraint($expectedMethodConstraint)
            ->hasProperty('prop')
            ->withConstraint($expectedPropertyConstraint);
        $this->assertSame([$expectedMethodConstraint], $this->constraints->getMethodConstraints(self::CLASS_NAME, 'method'));
        $this->assertSame([$expectedPropertyConstraint], $this->constraints->getPropertyConstraints(self::CLASS_NAME, 'prop'));
    }

    public function testAddingConstraintsToPropertyThenToAnotherPropertyAddsThemToCorrectField(): void
    {
        $expectedProperty1Constraint = $this->createMock(IValidationConstraint::class);
        $expectedProperty2Constraint = $this->createMock(IValidationConstraint::class);
        $this->constraintBuilder->hasProperty('prop1')
            ->withConstraint($expectedProperty1Constraint)
            ->hasProperty('prop2')
            ->withConstraint($expectedProperty2Constraint);
        $this->assertSame([$expectedProperty1Constraint], $this->constraints->getPropertyConstraints(self::CLASS_NAME, 'prop1'));
        $this->assertSame([$expectedProperty2Constraint], $this->constraints->getPropertyConstraints(self::CLASS_NAME, 'prop2'));
    }

    public function testAddingConstraintsToPropertyThenToMethodAddsThemToCorrectField(): void
    {
        $expectedMethodConstraint = $this->createMock(IValidationConstraint::class);
        $expectedPropertyConstraint = $this->createMock(IValidationConstraint::class);
        $this->constraintBuilder->hasProperty('prop')
            ->withConstraint($expectedPropertyConstraint)
            ->hasMethod('method')
            ->withConstraint($expectedMethodConstraint);
        $this->assertSame([$expectedMethodConstraint], $this->constraints->getMethodConstraints(self::CLASS_NAME, 'method'));
        $this->assertSame([$expectedPropertyConstraint], $this->constraints->getPropertyConstraints(self::CLASS_NAME, 'prop'));
    }

    public function testWithConstraintForMethodAddsConstraintToMethod(): void
    {
        $expectedConstraint = $this->createMock(IValidationConstraint::class);
        $this->constraintBuilder->hasMethod('method')
            ->withConstraint($expectedConstraint);
        $this->assertEquals([$expectedConstraint], $this->constraints->getMethodConstraints(self::CLASS_NAME, 'method'));
    }

    public function testWithConstraintForPropertyAddsConstraintToProperty(): void
    {
        $expectedConstraint = $this->createMock(IValidationConstraint::class);
        $this->constraintBuilder->hasProperty('prop')
            ->withConstraint($expectedConstraint);
        $this->assertEquals([$expectedConstraint], $this->constraints->getPropertyConstraints(self::CLASS_NAME, 'prop'));
    }

    public function testWithConstraintsForMethodAddsConstraintsToMethod(): void
    {
        $expectedConstraints = [
            $this->createMock(IValidationConstraint::class),
            $this->createMock(IValidationConstraint::class)
        ];
        $this->constraintBuilder->hasMethod('method')
            ->withConstraints($expectedConstraints);
        $this->assertEquals($expectedConstraints, $this->constraints->getMethodConstraints(self::CLASS_NAME, 'method'));
    }

    public function testWithConstraintsForPropertyAddsConstraintsToProperty(): void
    {
        $expectedConstraints = [
            $this->createMock(IValidationConstraint::class),
            $this->createMock(IValidationConstraint::class)
        ];
        $this->constraintBuilder->hasProperty('prop')
            ->withConstraints($expectedConstraints);
        $this->assertEquals($expectedConstraints, $this->constraints->getPropertyConstraints(self::CLASS_NAME, 'prop'));
    }

    public function testWithConstraintsWithoutSettingFieldThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Must call hasMethod() or hasProperty() before adding constraints');
        $this->constraintBuilder->withConstraint($this->createMock(IValidationConstraint::class));
    }

    public function testWithConstraintWithoutSettingFieldThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Must call hasMethod() or hasProperty() before adding constraints');
        $this->constraintBuilder->withConstraints([$this->createMock(IValidationConstraint::class)]);
    }
}
