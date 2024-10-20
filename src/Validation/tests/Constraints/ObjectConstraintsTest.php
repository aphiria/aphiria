<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraints;
use PHPUnit\Framework\TestCase;

class ObjectConstraintsTest extends TestCase
{
    public function testAddingMultipleMethodConstraintsMakesThemGettable(): void
    {
        $expectedConstraints = [$this->createMock(IConstraint::class)];
        $objectConstraints = new ObjectConstraints(self::class);
        $objectConstraints->addMethodConstraint('method', $expectedConstraints);
        $actualMethodConstraints = $objectConstraints->getConstraintsForMethod('method');
        $actualAllMethodConstraints = $objectConstraints->methodConstraints;
        $this->assertSame($expectedConstraints, $actualMethodConstraints);
        $this->assertTrue(isset($actualAllMethodConstraints['method']));
        $this->assertSame($expectedConstraints, $actualAllMethodConstraints['method']);
    }

    public function testAddingMultiplePropertyConstraintsMakesThemGettable(): void
    {
        $expectedConstraints = [$this->createMock(IConstraint::class)];
        $objectConstraints = new ObjectConstraints(self::class);
        $objectConstraints->addPropertyConstraint('prop', $expectedConstraints);
        $actualProperty = $objectConstraints->getConstraintsForProperty('prop');
        $actualAllPropertyConstraints = $objectConstraints->propertyConstraints;
        $this->assertSame($expectedConstraints, $actualProperty);
        $this->assertTrue(isset($actualAllPropertyConstraints['prop']));
        $this->assertSame($expectedConstraints, $actualAllPropertyConstraints['prop']);
    }

    public function testAddingSingleMethodConstraintsMakesItGettable(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints(self::class);
        $objectConstraints->addMethodConstraint('method', $expectedConstraint);
        $actualMethodConstraints = $objectConstraints->getConstraintsForMethod('method');
        $actualAllMethodConstraints = $objectConstraints->methodConstraints;
        $this->assertCount(1, $actualMethodConstraints);
        $this->assertSame($expectedConstraint, $actualMethodConstraints[0]);
        $this->assertTrue(isset($actualAllMethodConstraints['method']));
        $this->assertSame($expectedConstraint, $actualAllMethodConstraints['method'][0]);
    }

    public function testAddingSinglePropertyConstraintsMakesItGettable(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints(self::class);
        $objectConstraints->addPropertyConstraint('prop', $expectedConstraint);
        $actualPropertyConstraints = $objectConstraints->getConstraintsForProperty('prop');
        $actualAllPropertyConstraints = $objectConstraints->propertyConstraints;
        $this->assertCount(1, $actualPropertyConstraints);
        $this->assertSame($expectedConstraint, $actualPropertyConstraints[0]);
        $this->assertTrue(isset($actualAllPropertyConstraints['prop']));
        $this->assertSame($expectedConstraint, $actualAllPropertyConstraints['prop'][0]);
    }

    public function testGettingClassReturnsOneSetInConstructor(): void
    {
        $objectConstraints = new ObjectConstraints(self::class, [], []);
        $this->assertSame(self::class, $objectConstraints->className);
    }

    public function testGettingMethodConstraintsForMethodWithoutAnyReturnsEmptyArray(): void
    {
        $objectConstraints = new ObjectConstraints(self::class, [], []);
        $this->assertEquals([], $objectConstraints->methodConstraints);
        $this->assertEquals([], $objectConstraints->getConstraintsForMethod('method'));
    }

    public function testGettingMethodConstraintsReturnsOnesSetInConstructor(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints(self::class, [], ['method' => [$expectedConstraint]]);
        $this->assertEquals(['method' => [$expectedConstraint]], $objectConstraints->methodConstraints);
        $this->assertEquals([$expectedConstraint], $objectConstraints->getConstraintsForMethod('method'));
    }

    public function testGettingMethodConstraintsReturnsSingleOneSetInConstructor(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints(self::class, [], ['method' => $expectedConstraint]);
        $this->assertEquals(['method' => [$expectedConstraint]], $objectConstraints->methodConstraints);
        $this->assertEquals([$expectedConstraint], $objectConstraints->getConstraintsForMethod('method'));
    }

    public function testGettingPropertyConstraintsForPropertyWithoutAnyReturnsEmptyArray(): void
    {
        $objectConstraints = new ObjectConstraints(self::class, [], []);
        $this->assertEquals([], $objectConstraints->propertyConstraints);
        $this->assertEquals([], $objectConstraints->getConstraintsForProperty('prop'));
    }

    public function testGettingPropertyConstraintsReturnsOnesSetInConstructor(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints(self::class, ['prop' => [$expectedConstraint]], []);
        $this->assertEquals(['prop' => [$expectedConstraint]], $objectConstraints->propertyConstraints);
        $this->assertEquals([$expectedConstraint], $objectConstraints->getConstraintsForProperty('prop'));
    }

    public function testGettingPropertyConstraintsReturnsSingleOneSetInConstructor(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints(self::class, ['prop' => $expectedConstraint], []);
        $this->assertEquals(['prop' => [$expectedConstraint]], $objectConstraints->propertyConstraints);
        $this->assertEquals([$expectedConstraint], $objectConstraints->getConstraintsForProperty('prop'));
    }
}
