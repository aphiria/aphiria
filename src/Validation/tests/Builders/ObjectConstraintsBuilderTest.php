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

use Aphiria\Validation\Builders\ObjectConstraintsBuilder;
use Aphiria\Validation\Constraints\IConstraint;
use LogicException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the object constraints builder
 */
class ObjectConstraintsBuilderTest extends TestCase
{
    private ObjectConstraintsBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new ObjectConstraintsBuilder();
    }

    public function testHasMethodConstraintsWithMultipleConstraintsAddsThemToRegistry(): void
    {
        $expectedMethodConstraints = [$this->createMock(IConstraint::class), $this->createMock(IConstraint::class)];
        $objectConstraints = $this->builder->class('foo')
            ->hasMethodConstraints('method', $expectedMethodConstraints)
            ->build();
        $this->assertSame($expectedMethodConstraints, $objectConstraints->getMethodConstraints('foo', 'method'));
    }

    public function testHasMethodConstraintsWithSingleConstraintAddsItToRegistry(): void
    {
        $expectedMethodConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = $this->builder->class('foo')
            ->hasMethodConstraints('method', $expectedMethodConstraint)
            ->build();
        $this->assertSame([$expectedMethodConstraint], $objectConstraints->getMethodConstraints('foo', 'method'));
    }

    public function testHasPropertyConstraintsWithMultipleConstraintsAddsThemToRegistry(): void
    {
        $expectedPropertyConstraints = [$this->createMock(IConstraint::class), $this->createMock(IConstraint::class)];
        $objectConstraints = $this->builder->class('foo')
            ->hasPropertyConstraints('prop', $expectedPropertyConstraints)
            ->build();
        $this->assertSame($expectedPropertyConstraints, $objectConstraints->getPropertyConstraints('foo', 'prop'));
    }

    public function testHasPropertyConstraintsWithSingleConstraintAddsItToRegistry(): void
    {
        $expectedPropertyConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = $this->builder->class('foo')
            ->hasPropertyConstraints('prop', $expectedPropertyConstraint)
            ->build();
        $this->assertSame([$expectedPropertyConstraint], $objectConstraints->getPropertyConstraints('foo', 'prop'));
    }

    public function testNotCallingClassBeforeAddingMethodConstraintsThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Must call ' . ObjectConstraintsBuilder::class . '::class() before calling this method');
        $this->builder->hasMethodConstraints('method', []);
    }

    public function testNotCallingClassBeforeAddingPropertyConstraintsThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Must call ' . ObjectConstraintsBuilder::class . '::class() before calling this method');
        $this->builder->hasPropertyConstraints('prop', []);
    }
}
