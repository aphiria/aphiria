<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
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
        $middlewareBinding = new MiddlewareBinding('foo', $expectedAttributes);
        $this->assertSame('foo', $middlewareBinding->className);
        $this->assertSame($expectedAttributes, $middlewareBinding->attributes);
    }
}
