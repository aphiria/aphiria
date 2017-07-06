<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Middleware;

/**
 * Tests middleware bindings
 */
class MiddlewareBindingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the correct attributes are returned
     */
    public function testCorrectAttributesAreReturned() : void
    {
        $middlewareBinding = new MiddlewareBinding('foo', ['bar' => 'baz']);
        $this->assertEquals(['bar' => 'baz'], $middlewareBinding->getAttributes());
    }
    
    /**
     * Tests that the correct class name is returned
     */
    public function testCorrectClassNameIsReturned() : void
    {
        $middlewareBinding = new MiddlewareBinding('foo', ['bar' => 'baz']);
        $this->assertEquals('foo', $middlewareBinding->getClassName());
    }
}
