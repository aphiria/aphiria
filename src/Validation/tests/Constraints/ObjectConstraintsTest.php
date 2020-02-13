<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Tests\Constraints;

use Aphiria\Validation\Constraints\IConstraint;
use Aphiria\Validation\Constraints\ObjectConstraints;
use PHPUnit\Framework\TestCase;

/**
 * Tests object constraints
 */
class ObjectConstraintsTest extends TestCase
{
    public function testAddingMultipleMethodConstraintsMakesThemGettable(): void
    {
        $expectedConstraints = [$this->createMock(IConstraint::class)];
        $objectConstraints = new ObjectConstraints('foo');
        $objectConstraints->addMethodConstraint('method', $expectedConstraints);
        $actualMethodConstraints = $objectConstraints->getMethodConstraints('method');
        $actualAllMethodConstraints = $objectConstraints->getAllMethodConstraints();
        $this->assertSame($expectedConstraints, $actualMethodConstraints);
        $this->assertTrue(isset($actualAllMethodConstraints['method']));
        $this->assertSame($expectedConstraints, $actualAllMethodConstraints['method']);
    }

    public function testAddingSingleMethodConstraintsMakesItGettable(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints('foo');
        $objectConstraints->addMethodConstraint('method', $expectedConstraint);
        $actualMethodConstraints = $objectConstraints->getMethodConstraints('method');
        $actualAllMethodConstraints = $objectConstraints->getAllMethodConstraints();
        $this->assertCount(1, $actualMethodConstraints);
        $this->assertSame($expectedConstraint, $actualMethodConstraints[0]);
        $this->assertTrue(isset($actualAllMethodConstraints['method']));
        $this->assertSame($expectedConstraint, $actualAllMethodConstraints['method'][0]);
    }

    public function testAddingMultiplePropertyConstraintsMakesThemGettable(): void
    {
        $expectedConstraints = [$this->createMock(IConstraint::class)];
        $objectConstraints = new ObjectConstraints('foo');
        $objectConstraints->addPropertyConstraint('prop', $expectedConstraints);
        $actualProperty = $objectConstraints->getPropertyConstraints('prop');
        $actualAllPropertyConstraints = $objectConstraints->getAllPropertyConstraints();
        $this->assertSame($expectedConstraints, $actualProperty);
        $this->assertTrue(isset($actualAllPropertyConstraints['prop']));
        $this->assertSame($expectedConstraints, $actualAllPropertyConstraints['prop']);
    }

    public function testAddingSinglePropertyConstraintsMakesItGettable(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints('foo');
        $objectConstraints->addPropertyConstraint('prop', $expectedConstraint);
        $actualPropertyConstraints = $objectConstraints->getPropertyConstraints('prop');
        $actualAllPropertyConstraints = $objectConstraints->getAllPropertyConstraints();
        $this->assertCount(1, $actualPropertyConstraints);
        $this->assertSame($expectedConstraint, $actualPropertyConstraints[0]);
        $this->assertTrue(isset($actualAllPropertyConstraints['prop']));
        $this->assertSame($expectedConstraint, $actualAllPropertyConstraints['prop'][0]);
    }

    public function testGettingClassReturnsOneSetInConstructor(): void
    {
        $objectConstraints = new ObjectConstraints('foo', [], []);
        $this->assertEquals('foo', $objectConstraints->getClassName());
    }

    public function testGettingMethodConstraintsForMethodWithoutAnyReturnsEmptyArray(): void
    {
        $objectConstraints = new ObjectConstraints('foo', [], []);
        $this->assertEquals([], $objectConstraints->getAllMethodConstraints());
        $this->assertEquals([], $objectConstraints->getMethodConstraints('method'));
    }

    public function testGettingMethodConstraintsReturnsOnesSetInConstructor(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints('foo', [], ['method' => [$expectedConstraint]]);
        $this->assertEquals(['method' => [$expectedConstraint]], $objectConstraints->getAllMethodConstraints());
        $this->assertEquals([$expectedConstraint], $objectConstraints->getMethodConstraints('method'));
    }

    public function testGettingMethodConstraintsReturnsSingleOneSetInConstructor(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints('foo', [], ['method' => $expectedConstraint]);
        $this->assertEquals(['method' => [$expectedConstraint]], $objectConstraints->getAllMethodConstraints());
        $this->assertEquals([$expectedConstraint], $objectConstraints->getMethodConstraints('method'));
    }

    public function testGettingPropertyConstraintsForPropertyWithoutAnyReturnsEmptyArray(): void
    {
        $objectConstraints = new ObjectConstraints('foo', [], []);
        $this->assertEquals([], $objectConstraints->getAllPropertyConstraints());
        $this->assertEquals([], $objectConstraints->getPropertyConstraints('prop'));
    }

    public function testGettingPropertyConstraintsReturnsOnesSetInConstructor(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints('foo', ['prop' => [$expectedConstraint]], []);
        $this->assertEquals(['prop' => [$expectedConstraint]], $objectConstraints->getAllPropertyConstraints());
        $this->assertEquals([$expectedConstraint], $objectConstraints->getPropertyConstraints('prop'));
    }

    public function testGettingPropertyConstraintsReturnsSingleOneSetInConstructor(): void
    {
        $expectedConstraint = $this->createMock(IConstraint::class);
        $objectConstraints = new ObjectConstraints('foo', ['prop' => $expectedConstraint], []);
        $this->assertEquals(['prop' => [$expectedConstraint]], $objectConstraints->getAllPropertyConstraints());
        $this->assertEquals([$expectedConstraint], $objectConstraints->getPropertyConstraints('prop'));
    }
}
