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
use Aphiria\Validation\Constraints\ObjectConstraints;
use PHPUnit\Framework\TestCase;

/**
 * Tests object constraints
 */
class ObjectConstraintsTest extends TestCase
{
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
