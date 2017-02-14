<?php
namespace Opulence\Router;

use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\UriTemplates\IUriTemplate;

/**
 * Tests the routes
 */
class RouteTest extends \PHPUnit\Framework\TestCase
{
    private const ROUTE_NAME = 'route';
    /** @var Route The route to use in tests */
    private $route = null;
    /** @var IUriTemplate|\PHPUnit_Framework_MockObject_MockObject The URI template used by the route */
    private $uriTemplate = null;
    /** @var RouteAction|\PHPUnit_Framework_MockObject_MockObject The mocked route action */
    private $routeAction = null;
    /** @var MiddlewareBinding[] The list of middleware bindings in the matched route */
    private $middlewareBindings = [];
    
    /**
     * Sets up the tests
     */
    public function setUp()
    {
        $this->routeAction = $this->createMock(RouteAction::class);
        $this->middlewareBindings = [new MiddlewareBinding('Foo')];
        $this->uriTemplate = $this->createMock(IUriTemplate::class);
        $this->route = new Route(
            ['GET'],
            $this->routeAction,
            $this->uriTemplate,
            true,
            $this->middlewareBindings,
            self::ROUTE_NAME
        );
    }
    
    /**
     * Tests that the correct action is returned
     */
    public function testCorrectActionIsReturned()
    {
        $this->assertSame($this->routeAction, $this->route->getAction());
    }
    
    /**
     * Tests that the correct HTTP methods are returned
     */
    public function testCorrectHttpMethodsAreReturned()
    {
        $this->assertEquals(['GET'], $this->route->getHttpMethods());
    }
    
    /**
     * Tests that the correct middleware bindings are returned
     */
    public function testCorrectMiddlewareBindingsAreReturned()
    {
        $this->assertSame($this->middlewareBindings, $this->route->getMiddlewareBindings());
    }
    
    /**
     * Tests that the correct name is returned
     */
    public function testCorrectNameIsReturned()
    {
        $this->assertEquals(self::ROUTE_NAME, $this->route->getName());
    }
    
    /**
     * Tests that the correct URI template is returned
     */
    public function testCorrectUriTemplateIsReturned()
    {
        $this->assertSame($this->uriTemplate, $this->route->getUriTemplate());
    }
    
    /**
     * Tests that the HTTPS flag is set correctly
     */
    public function testHttpsFlagIsSetCorrectly()
    {
        $this->assertTrue($this->route->isHttpsOnly());
    }
    
    /**
     * Tests that the name defaults to null
     */
    public function testNameDefaultsToNull()
    {
        $this->routeAction = $this->createMock(RouteAction::class);
        $this->middlewareBindings = [new MiddlewareBinding('Foo')];
        $this->uriTemplate = $this->createMock(IUriTemplate::class);
        $namelessRoute = new Route(
            ['GET'],
            $this->routeAction,
            $this->uriTemplate,
            true,
            $this->middlewareBindings
        );
        $this->assertNull($namelessRoute->getName());
    }
}
