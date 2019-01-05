<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Middleware;

use Opulence\Routing\Middleware\MiddlewareBinding;

/**
 * Tests middleware bindings
 */
class MiddlewareBindingTest extends \PHPUnit\Framework\TestCase
{
    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedAttributes = ['bar' => 'baz'];
        $middlewareBinding = new MiddlewareBinding('foo', $expectedAttributes);
        $this->assertEquals('foo', $middlewareBinding->className);
        $this->assertSame($expectedAttributes, $middlewareBinding->attributes);
    }
}
