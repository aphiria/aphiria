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

use Aphiria\Validation\ConstraintRegistry;
use Aphiria\Validation\Constraints\IValidationConstraint;
use PHPUnit\Framework\TestCase;

/**
 * Tests the constraint registry
 */
class ConstraintRegistryTest extends TestCase
{
    private ConstraintRegistry $constraints;

    protected function setUp(): void
    {
        $this->constraints = new ConstraintRegistry();
    }

    public function testGettingAllMethodConstraintsForClassWithNoConstraintsReturnsEmptyArray(): void
    {
        $this->assertCount(0, $this->constraints->getAllMethodConstraints(\get_class($this)));
    }

    public function testGettingAllMethodConstraintsForClassWithConstraintsReturnsThem(): void
    {
        $constraint1 = $this->createMock(IValidationConstraint::class);
        $constraint2 = $this->createMock(IValidationConstraint::class);
        $this->constraints->registerMethodConstraints(\get_class($this), 'foo', [$constraint1, $constraint2]);
        $this->assertEquals(
            ['foo' => [$constraint1, $constraint2]],
            $this->constraints->getAllMethodConstraints(\get_class($this))
        );
    }

    public function testGettingAllPropertyConstraintsForClassWithNoConstraintsReturnsEmptyArray(): void
    {
        $this->assertCount(0, $this->constraints->getAllPropertyConstraints(\get_class($this)));
    }

    public function testGettingAllPropertyConstraintsForClassWithConstraintsReturnsThem(): void
    {
        $constraint1 = $this->createMock(IValidationConstraint::class);
        $constraint2 = $this->createMock(IValidationConstraint::class);
        $this->constraints->registerPropertyConstraints(\get_class($this), 'foo', [$constraint1, $constraint2]);
        $this->assertEquals(
            ['foo' => [$constraint1, $constraint2]],
            $this->constraints->getAllPropertyConstraints(\get_class($this))
        );
    }

    public function testGettingMethodConstraintsForClassWithNoConstraintsReturnsEmptyArray(): void
    {
        $this->assertCount(0, $this->constraints->getMethodConstraints(\get_class($this), 'foo'));
    }

    public function testGettingMethodConstraintsForClassWithConstraintsReturnsThem(): void
    {
        $constraint = $this->createMock(IValidationConstraint::class);
        $this->constraints->registerMethodConstraints(\get_class($this), 'foo', $constraint);
        $this->assertEquals([$constraint], $this->constraints->getMethodConstraints(\get_class($this), 'foo'));
    }

    public function testGettingPropertyConstraintsForClassWithNoConstraintsReturnsEmptyArray(): void
    {
        $this->assertCount(0, $this->constraints->getPropertyConstraints(\get_class($this), 'foo'));
    }

    public function testGettingPropertyConstraintsForClassWithConstraintsReturnsThem(): void
    {
        $constraint = $this->createMock(IValidationConstraint::class);
        $this->constraints->registerPropertyConstraints(\get_class($this), 'foo', $constraint);
        $this->assertEquals([$constraint], $this->constraints->getPropertyConstraints(\get_class($this), 'foo'));
    }

    public function testRegisteringMultipleMethodConstraintsWorks(): void
    {
        $constraint1 = $this->createMock(IValidationConstraint::class);
        $constraint2 = $this->createMock(IValidationConstraint::class);
        $this->constraints->registerMethodConstraints(\get_class($this), 'foo', [$constraint1, $constraint2]);
        $this->assertEquals(
            ['foo' => [$constraint1, $constraint2]],
            $this->constraints->getAllMethodConstraints(\get_class($this))
        );
    }

    public function testRegisteringSingleMethodConstraintWorks(): void
    {
        $constraint = $this->createMock(IValidationConstraint::class);
        $this->constraints->registerMethodConstraints(\get_class($this), 'foo', $constraint);
        $this->assertEquals([$constraint], $this->constraints->getMethodConstraints(\get_class($this), 'foo'));
    }

    public function testRegisteringMultiplePropertyConstraintsWorks(): void
    {
        $constraint1 = $this->createMock(IValidationConstraint::class);
        $constraint2 = $this->createMock(IValidationConstraint::class);
        $this->constraints->registerPropertyConstraints(\get_class($this), 'foo', [$constraint1, $constraint2]);
        $this->assertEquals(
            ['foo' => [$constraint1, $constraint2]],
            $this->constraints->getAllPropertyConstraints(\get_class($this))
        );
    }

    public function testRegisteringSinglePropertyConstraintWorks(): void
    {
        $constraint = $this->createMock(IValidationConstraint::class);
        $this->constraints->registerPropertyConstraints(\get_class($this), 'foo', $constraint);
        $this->assertEquals([$constraint], $this->constraints->getPropertyConstraints(\get_class($this), 'foo'));
    }
}
