<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
        /**
         * @psalm-suppress UndefinedClass Intentionally testing an empty string
         * @psalm-suppress ArgumentTypeCoercion Ditto
         */
        new Middleware('');
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $middleware = new class () {
        };
        $middlewareAttribute = new Middleware($middleware::class, ['foo' => 'bar']);
        $this->assertSame($middleware::class, $middlewareAttribute->className);
        $this->assertSame(['foo' => 'bar'], $middlewareAttribute->parameters);
    }
}
