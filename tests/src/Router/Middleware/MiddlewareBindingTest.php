<?php
namespace Opulence\Router\Middleware;

/**
 * Tests middleware bindings
 */
class MiddlewareBindingTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the correct class name is returned
     */
    public function testCorrectClassNameIsReturned() : void
    {
        $middlewareBinding = new MiddlewareBinding('foo', ['bar' => 'baz']);
        $this->assertEquals('foo', $middlewareBinding->getClassName());
    }

    /**
     * Tests that the correct properties are returned
     */
    public function testCorrectPropertiesAreReturned() : void
    {
        $middlewareBinding = new MiddlewareBinding('foo', ['bar' => 'baz']);
        $this->assertEquals(['bar' => 'baz'], $middlewareBinding->getProperties());
    }
}
