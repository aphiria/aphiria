<?php
namespace Opulence\Router\Builders;

use Opulence\Router\Middleware\MiddlewareBinding;

/**
 * Tests the route group options
 */
class RouteGroupOptionsTest extends \PHPUnit\Framework\TestCase
{
    /** @var RouteGroupOptions The options to use in tests */
    private $routeGroupOptions = null;
    /** @var MiddlewareBinding[] The list of middleware bindings in the options */
    private $middlewareBindings = [];

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->middlewareBindings = [new MiddlewareBinding('foo')];
        $this->routeGroupOptions = new RouteGroupOptions(
            'path',
            'host',
            true,
            $this->middlewareBindings,
            ['foo' => 'bar']
        );
    }

    /**
     * Tests that the correct headers to match are returned
     */
    public function testCorrectHeadersToMatchAreReturned() : void
    {
        $this->assertEquals(['foo' => 'bar'], $this->routeGroupOptions->getHeadersToMatch());
    }

    /**
     * Tests that the correct host is returned
     */
    public function testCorrectHostIsReturned() : void
    {
        $this->assertEquals('host', $this->routeGroupOptions->getHostTemplate());
    }

    /**
     * Tests that the correct HTTPS-only setting is returned
     */
    public function testCorrectHttpsOnlySettingIsReturned() : void
    {
        $this->assertTrue($this->routeGroupOptions->isHttpsOnly());
    }

    /**
     * Tests that the correct middleware bindings are returned
     */
    public function testCorrectMiddlewareBindingsAreReturned() : void
    {
        $this->assertEquals($this->middlewareBindings, $this->routeGroupOptions->getMiddlewareBindings());
    }

    /**
     * Tests that the correct path is returned
     */
    public function testCorrectPathIsReturned() : void
    {
        $this->assertEquals('path', $this->routeGroupOptions->getPathTemplate());
    }
}
