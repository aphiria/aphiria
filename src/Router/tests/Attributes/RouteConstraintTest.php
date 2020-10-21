<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Routing\Attributes\RouteConstraint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RouteConstraintTest extends TestCase
{
    public function testEmptyClassNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be set');
        new RouteConstraint('');
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $constraint = new RouteConstraint('foo', ['bar']);
        $this->assertSame('foo', $constraint->className);
        $this->assertSame(['bar'], $constraint->constructorParameters);
    }
}
