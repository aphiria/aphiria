<?php
namespace Opulence\Router;

use Opulence\Router\Middleware\MiddlewareBinding;

/**
 * Tests the matched routes
 */
class MatchedRouteTest extends \PHPUnit\Framework\TestCase
{
    /** @var MatchedRoute The matched route to use in tests */
    private $matchedRoute = null;
    /** @var RouteAction|\PHPUnit_Framework_MockObject_MockObject The mocked route action */
    private $routeAction = null;
    /** @var MiddlewareBinding[] The list of middleware bindings in the matched route */
    private $middlewareBindings = [];
    /** @var array The route vars in the matched route */
    private $routeVars = [];

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->routeAction = $this->createMock(RouteAction::class);
        $this->middlewareBindings = [new MiddlewareBinding('Foo')];
        $this->routeVars = ['foo' => 'bar'];
        $this->matchedRoute = new MatchedRoute($this->routeAction, $this->routeVars, $this->middlewareBindings);
    }

    /**
     * Tests that the correct action is returned
     */
    public function testCorrectActionIsReturned() : void
    {
        $this->assertSame($this->routeAction, $this->matchedRoute->getAction());
    }

    /**
     * Tests that the correct middleware bindings are returned
     */
    public function testCorrectMiddlewareBindingsAreReturned() : void
    {
        $this->assertSame($this->middlewareBindings, $this->matchedRoute->getMiddlewareBindings());
    }

    /**
     * Tests that the correct route vars are returned
     */
    public function testCorrectRouteVarsAreReturned() : void
    {
        $this->assertSame($this->routeVars, $this->matchedRoute->getRouteVars());
    }
}
