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

use Aphiria\Routing\Attributes\Middleware;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    public function testEmptyClassNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be set');
        new Middleware('');
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $middleware = new Middleware('foo', ['foo' => 'bar']);
        $this->assertSame('foo', $middleware->className);
        $this->assertSame(['foo' => 'bar'], $middleware->attributes);
    }
}
