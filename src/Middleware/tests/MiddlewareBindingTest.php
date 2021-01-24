<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Tests;

use Aphiria\Middleware\MiddlewareBinding;
use PHPUnit\Framework\TestCase;

class MiddlewareBindingTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedAttributes = ['bar' => 'baz'];
        $middleware = new class() {
        };
        $middlewareBinding = new MiddlewareBinding($middleware::class, $expectedAttributes);
        $this->assertSame($middleware::class, $middlewareBinding->className);
        $this->assertSame($expectedAttributes, $middlewareBinding->parameters);
    }
}
