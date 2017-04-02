<?php
namespace Opulence\Router;

use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\UriTemplates\IUriTemplate;

/**
 * Tests the routes
 */
class RouteTest extends \PHPUnit\Framework\TestCase
{
    /** @var The name of the route to use */
    private const ROUTE_NAME = 'route';
    /** @var The list of header values to match on */
    private static $headersToMatch = ['foo' => 'bar'];
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
    public function setUp() : void
    {
        $this->routeAction = $this->createMock(RouteAction::class);
        $this->middlewareBindings = [new MiddlewareBinding('Foo')];
        $this->uriTemplate = $this->createMock(IUriTemplate::class);
        $this->route = new Route(
            ['GET'],
            $this->uriTemplate,
            $this->routeAction,
            $this->middlewareBindings,
            self::ROUTE_NAME,
            self::$headersToMatch
        );
    }

    /**
     * Tests that the correct action is returned
     */
    public function testCorrectActionIsReturned() : void
    {
        $this->assertSame($this->routeAction, $this->route->getAction());
    }

    /**
     * Tests that the correct header values are returned
     */
    public function testCorrectHeaderValuesAreReturned() : void
    {
        $this->assertSame(self::$headersToMatch, $this->route->getHeadersToMatch());
    }

    /**
     * Tests that the correct HTTP methods are returned
     */
    public function testCorrectHttpMethodsAreReturned() : void
    {
        $this->assertEquals(['GET'], $this->route->getHttpMethods());
    }

    /**
     * Tests that the correct middleware bindings are returned
     */
    public function testCorrectMiddlewareBindingsAreReturned() : void
    {
        $this->assertSame($this->middlewareBindings, $this->route->getMiddlewareBindings());
    }

    /**
     * Tests that the correct name is returned
     */
    public function testCorrectNameIsReturned() : void
    {
        $this->assertEquals(self::ROUTE_NAME, $this->route->getName());
    }

    /**
     * Tests that the correct URI template is returned
     */
    public function testCorrectUriTemplateIsReturned() : void
    {
        $this->assertSame($this->uriTemplate, $this->route->getUriTemplate());
    }

    /**
     * Tests that the name defaults to null
     */
    public function testNameDefaultsToNull() : void
    {
        $this->routeAction = $this->createMock(RouteAction::class);
        $this->middlewareBindings = [new MiddlewareBinding('Foo')];
        $this->uriTemplate = $this->createMock(IUriTemplate::class);
        $namelessRoute = new Route(
            ['GET'],
            $this->uriTemplate,
            $this->routeAction,
            $this->middlewareBindings
        );
        $this->assertNull($namelessRoute->getName());
    }
}
