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
use PHPUnit\Framework\TestCase;

/**
 * Tests the object constraints builder
 */
class ObjectConstraintsBuilderTest extends TestCase
{
    private ObjectConstraintsBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new ObjectConstraintsBuilder('foo');
    }

    public function testBuiltConstraintsHasCorrectClassName(): void
    {
        $this->assertEquals('foo', $this->builder->build()->getClassName());
    }

    public function testHasMethodConstraintsWithMultipleConstraintsAddsThemToRegistry(): void
    {
        $expectedMethodConstraints = [$this->createMock(IConstraint::class), $this->createMock(IConstraint::class)];
        $objectConstraints = $this->builder->hasMethodConstraints('method', $expectedMethodConstraints)
            ->build();
        $this->assertSame($expectedMethodConstraints, $objectConstraints->getMethodConstraints('method'));
    }

    public function testHasMethodConstraintsWithSingleConstraintAddsItToRegistry(): void
    {
        $expectedMethodConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = $this->builder->hasMethodConstraints('method', $expectedMethodConstraint)
            ->build();
        $this->assertSame([$expectedMethodConstraint], $objectConstraints->getMethodConstraints('method'));
    }

    public function testHasPropertyConstraintsWithMultipleConstraintsAddsThemToRegistry(): void
    {
        $expectedPropertyConstraints = [$this->createMock(IConstraint::class), $this->createMock(IConstraint::class)];
        $objectConstraints = $this->builder->hasPropertyConstraints('prop', $expectedPropertyConstraints)
            ->build();
        $this->assertSame($expectedPropertyConstraints, $objectConstraints->getPropertyConstraints('prop'));
    }

    public function testHasPropertyConstraintsWithSingleConstraintAddsItToRegistry(): void
    {
        $expectedPropertyConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = $this->builder->hasPropertyConstraints('prop', $expectedPropertyConstraint)
            ->build();
        $this->assertSame([$expectedPropertyConstraint], $objectConstraints->getPropertyConstraints('prop'));
    }
}
