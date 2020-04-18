<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Annotations;

use Aphiria\Routing\Annotations\Middleware;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    public function testAttributesCanBeSetFromAttributes(): void
    {
        $middleware = new Middleware(['className' => 'foo', 'attributes' => ['foo' => 'bar']]);
        $this->assertEquals(['foo' => 'bar'], $middleware->attributes);
    }

    public function testAttributesDefaultToEmptyArrayWhenNotSpecified(): void
    {
        $this->assertEquals([], (new Middleware(['className' => 'foo']))->attributes);
    }

    public function testClassNameCanBeSetFromClassName(): void
    {
        $this->assertEquals('foo', (new Middleware(['className' => 'foo']))->className);
    }

    public function testClassNameCanBeSetFromValue(): void
    {
        $this->assertEquals('foo', (new Middleware(['value' => 'foo']))->className);
    }

    public function testEmptyClassNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be set');
        new Middleware([]);
    }
}
