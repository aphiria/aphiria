<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraintRegistry;
use PHPUnit\Framework\TestCase;

/**
 * Tests the object constraint registry
 */
class ObjectConstraintRegistryTest extends TestCase
{
    private ObjectConstraintRegistry $objectConstraints;

    protected function setUp(): void
    {
        $this->objectConstraints = new ObjectConstraintRegistry();
    }

    public function testCopyEffectivelyDuplicatesAnotherRegistry(): void
    {
        $registry1 = new ObjectConstraintRegistry();
        $registry2 = new ObjectConstraintRegistry();
        $expectedPropertyConstraints = ['prop' => [$this->createMock(IConstraint::class)]];
        $expectedMethodConstraints = ['method' => [$this->createMock(IConstraint::class)]];
        $registry1->registerObjectConstraints('foo', $expectedPropertyConstraints, $expectedMethodConstraints);
        $registry2->copy($registry1);
        $this->assertSame($expectedPropertyConstraints, $registry2->getAllPropertyConstraints('foo'));
        $this->assertSame($expectedMethodConstraints, $registry2->getAllMethodConstraints('foo'));
    }

    public function testGettingConstraintsForAllMethodsReturnsThem(): void
    {
        $expectedMethodConstraints = ['method' => [$this->createMock(IConstraint::class)]];
        $this->objectConstraints->registerObjectConstraints('foo', [], $expectedMethodConstraints);
        $this->assertSame($expectedMethodConstraints, $this->objectConstraints->getAllMethodConstraints('foo'));
    }

    public function testGettingConstraintsForAllMethodsWhenThereAreNotAnyEmptyArray(): void
    {
        $this->objectConstraints->registerObjectConstraints('foo', [], []);
        $this->assertCount(0, $this->objectConstraints->getAllMethodConstraints('foo'));
        $this->assertCount(0, $this->objectConstraints->getAllMethodConstraints('bar'));
    }

    public function testGettingConstraintsForAllPropertiesReturnsThem(): void
    {
        $expectedPropertyConstraints = ['prop' => [$this->createMock(IConstraint::class)]];
        $this->objectConstraints->registerObjectConstraints('foo', $expectedPropertyConstraints, []);
        $this->assertSame($expectedPropertyConstraints, $this->objectConstraints->getAllPropertyConstraints('foo'));
    }

    public function testGettingConstraintsForAllPropertiesWhenThereAreNotAnyEmptyArray(): void
    {
        $this->objectConstraints->registerObjectConstraints('foo', [], []);
        $this->assertCount(0, $this->objectConstraints->getAllPropertyConstraints('foo'));
        $this->assertCount(0, $this->objectConstraints->getAllPropertyConstraints('bar'));
    }

    public function testGettingConstraintsForMethodThatDoesNotHaveSomeReturnsEmptyArray(): void
    {
        $this->objectConstraints->registerObjectConstraints('foo', [], []);
        $this->assertCount(0, $this->objectConstraints->getMethodConstraints('foo', 'method1'));
        $this->assertCount(0, $this->objectConstraints->getMethodConstraints('bar', 'method1'));
    }

    public function testGettingConstraintsForMethodThatHasSomeReturnsThem(): void
    {
        $expectedMethodConstraints = ['method' => [$this->createMock(IConstraint::class)]];
        $this->objectConstraints->registerObjectConstraints('foo', [], $expectedMethodConstraints);
        $this->assertSame(
            $expectedMethodConstraints['method'],
            $this->objectConstraints->getMethodConstraints('foo', 'method')
        );
    }

    public function testGettingConstraintsForPropertyThatDoesNotHaveSomeReturnsEmptyArray(): void
    {
        $this->objectConstraints->registerObjectConstraints('foo', [], []);
        $this->assertCount(0, $this->objectConstraints->getPropertyConstraints('foo', 'prop1'));
        $this->assertCount(0, $this->objectConstraints->getPropertyConstraints('bar', 'prop1'));

    }

    public function testGettingConstraintsForPropertyThatHasSomeReturnsThem(): void
    {
        $expectedPropertyConstraints = ['prop' => [$this->createMock(IConstraint::class)]];
        $this->objectConstraints->registerObjectConstraints('foo', $expectedPropertyConstraints, []);
        $this->assertSame(
            $expectedPropertyConstraints['prop'],
            $this->objectConstraints->getPropertyConstraints('foo', 'prop')
        );
    }

    public function testRegisteringConstraintsForMethodThatAlreadyHasSomeAppendsNewConstraints(): void
    {
        $methodConstraint1 = $this->createMock(IConstraint::class);
        $methodConstraint2 = $this->createMock(IConstraint::class);
        $this->objectConstraints->registerObjectConstraints('foo', [], ['method' => [$methodConstraint1]]);
        $this->objectConstraints->registerObjectConstraints('foo', [], ['method' => [$methodConstraint2]]);
        $this->assertSame(
            [$methodConstraint1, $methodConstraint2],
            $this->objectConstraints->getMethodConstraints('foo', 'method')
        );
    }

    public function testRegisteringConstraintsForPropertyThatAlreadyHasSomeAppendsNewConstraints(): void
    {
        $propertyConstraint1 = $this->createMock(IConstraint::class);
        $propertyConstraint2 = $this->createMock(IConstraint::class);
        $this->objectConstraints->registerObjectConstraints('foo', ['prop' => [$propertyConstraint1]], []);
        $this->objectConstraints->registerObjectConstraints('foo', ['prop' => [$propertyConstraint2]], []);
        $this->assertSame(
            [$propertyConstraint1, $propertyConstraint2],
            $this->objectConstraints->getPropertyConstraints('foo', 'prop')
        );
    }

    public function testRegisteringMultipleMethodConstraintIsAccepted(): void
    {
        $expectedMethodConstraints = [$this->createMock(IConstraint::class), $this->createMock(IConstraint::class)];
        $this->objectConstraints->registerObjectConstraints('foo', [], ['method' => $expectedMethodConstraints]);
        $this->assertSame($expectedMethodConstraints, $this->objectConstraints->getMethodConstraints('foo', 'method'));
    }

    public function testRegisteringMultiplePropertyConstraintIsAccepted(): void
    {
        $expectedPropertyConstraints = [$this->createMock(IConstraint::class), $this->createMock(IConstraint::class)];
        $this->objectConstraints->registerObjectConstraints('foo', ['prop' => $expectedPropertyConstraints], []);
        $this->assertSame($expectedPropertyConstraints, $this->objectConstraints->getPropertyConstraints('foo', 'prop'));
    }

    public function testRegisteringSingleMethodConstraintIsAccepted(): void
    {
        $expectedMethodConstraint = $this->createMock(IConstraint::class);
        $this->objectConstraints->registerObjectConstraints('foo', [], ['method' => $expectedMethodConstraint]);
        $this->assertCount(1, $this->objectConstraints->getMethodConstraints('foo', 'method'));
        $this->assertSame($expectedMethodConstraint, $this->objectConstraints->getMethodConstraints('foo', 'method')[0]);
    }

    public function testRegisteringSinglePropertyConstraintIsAccepted(): void
    {
        $expectedPropertyConstraint = $this->createMock(IConstraint::class);
        $this->objectConstraints->registerObjectConstraints('foo', ['prop' => $expectedPropertyConstraint], []);
        $this->assertCount(1, $this->objectConstraints->getPropertyConstraints('foo', 'prop'));
        $this->assertSame($expectedPropertyConstraint, $this->objectConstraints->getPropertyConstraints('foo', 'prop')[0]);
    }
}
