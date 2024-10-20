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

use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\ObjectConstraintsBuilder;
use PHPUnit\Framework\TestCase;

class ObjectConstraintsBuilderTest extends TestCase
{
    private ObjectConstraintsBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new ObjectConstraintsBuilder(self::class);
    }

    public function testBuiltConstraintsHasCorrectClassName(): void
    {
        $this->assertSame(self::class, $this->builder->build()->className);
    }

    public function testHasMethodConstraintsWithMultipleConstraintsAddsThemToRegistry(): void
    {
        $expectedMethodConstraints = [$this->createMock(IConstraint::class), $this->createMock(IConstraint::class)];
        $objectConstraints = $this->builder->hasMethodConstraints('method', $expectedMethodConstraints)
            ->build();
        $this->assertSame($expectedMethodConstraints, $objectConstraints->getConstraintsForMethod('method'));
    }

    public function testHasMethodConstraintsWithSingleConstraintAddsItToRegistry(): void
    {
        $expectedMethodConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = $this->builder->hasMethodConstraints('method', $expectedMethodConstraint)
            ->build();
        $this->assertSame([$expectedMethodConstraint], $objectConstraints->getConstraintsForMethod('method'));
    }

    public function testHasPropertyConstraintsWithMultipleConstraintsAddsThemToRegistry(): void
    {
        $expectedPropertyConstraints = [$this->createMock(IConstraint::class), $this->createMock(IConstraint::class)];
        $objectConstraints = $this->builder->hasPropertyConstraints('prop', $expectedPropertyConstraints)
            ->build();
        $this->assertSame($expectedPropertyConstraints, $objectConstraints->getConstraintsForProperty('prop'));
    }

    public function testHasPropertyConstraintsWithSingleConstraintAddsItToRegistry(): void
    {
        $expectedPropertyConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = $this->builder->hasPropertyConstraints('prop', $expectedPropertyConstraint)
            ->build();
        $this->assertSame([$expectedPropertyConstraint], $objectConstraints->getConstraintsForProperty('prop'));
    }
}
