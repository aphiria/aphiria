<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Routing\Attributes\ExcludeMiddleware;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ExcludeMiddlewareTest extends TestCase
{
    public function testEmptyClassNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class name must be set');
        /**
         * @psalm-suppress UndefinedClass Intentionally testing an empty string
         * @psalm-suppress ArgumentTypeCoercion Ditto
         */
        new ExcludeMiddleware('');
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $middleware = new class () {};
        $excludeMiddlewareAttribute = new ExcludeMiddleware($middleware::class, ['foo' => 'bar']);
        $this->assertSame($middleware::class, $excludeMiddlewareAttribute->className);
        $this->assertSame(['foo' => 'bar'], $excludeMiddlewareAttribute->parameters);
    }
}