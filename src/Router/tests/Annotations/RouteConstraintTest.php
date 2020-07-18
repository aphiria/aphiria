<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Annotations;

use Aphiria\Routing\Annotations\RouteConstraint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RouteConstraintTest extends TestCase
{
    public function testConstructorParamsCanBeSetFromConstructorParams(): void
    {
        $constraint = new RouteConstraint(['className' => 'foo', 'constructorParams' => ['foo']]);
        $this->assertEquals(['foo'], $constraint->constructorParams);
    }

    public function testConstructorParamsDefaultToEmptyArrayWhenNotSpecified(): void
    {
        $this->assertEquals([], (new RouteConstraint(['className' => 'foo']))->constructorParams);
    }

    public function testClassNameCanBeSetFromClassName(): void
    {
        $this->assertEquals('foo', (new RouteConstraint(['className' => 'foo']))->className);
    }

    public function testClassNameCanBeSetFromValue(): void
    {
        $this->assertEquals('foo', (new RouteConstraint(['value' => 'foo']))->className);
    }

    public function testEmptyClassNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be set');
        new RouteConstraint([]);
    }
}
