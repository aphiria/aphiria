<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Middleware;

use Aphiria\Routing\Middleware\MiddlewareBinding;
use PHPUnit\Framework\TestCase;

class MiddlewareBindingTest extends TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedParameters = ['bar' => 'baz'];
        $middleware = new class () {
        };
        $middlewareBinding = new MiddlewareBinding($middleware::class, $expectedParameters);
        $this->assertSame($middleware::class, $middlewareBinding->className);
        $this->assertSame($expectedParameters, $middlewareBinding->parameters);
    }
}
