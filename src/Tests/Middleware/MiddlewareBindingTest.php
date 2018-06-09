<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Middleware;

use Opulence\Routing\Middleware\MiddlewareBinding;

/**
 * Tests middleware bindings
 */
class MiddlewareBindingTest extends \PHPUnit\Framework\TestCase
{
    public function testCorrectAttributesAreReturned(): void
    {
        $middlewareBinding = new MiddlewareBinding('foo', ['bar' => 'baz']);
        $this->assertEquals(['bar' => 'baz'], $middlewareBinding->getAttributes());
    }

    public function testCorrectClassNameIsReturned(): void
    {
        $middlewareBinding = new MiddlewareBinding('foo', ['bar' => 'baz']);
        $this->assertEquals('foo', $middlewareBinding->getClassName());
    }
}
