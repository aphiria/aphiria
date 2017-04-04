<?php
namespace Opulence\Router;

use InvalidArgumentException;
use Opulence\Router\Matchers\IRouteMatcher;
use stdClass;

/**
 * Tests the router
 */
class RouterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that passing an array of routes does not throw an exception
     */
    public function testArrayOfRoutesDoesNotThrowException() : void
    {
        new Router([$this->createMock(Route::class)]);
    }

    /**
     * Tests that passing an invalid type for the routes throws an exception
     */
    public function testInvalidRoutesTypeThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new Router(new stdClass());
    }

    /**
     * Tests that no matches throws an exception
     */
    public function testNoMatchesThrowsException() : void
    {
        $this->expectException(RouteNotFoundException::class);
        $routes = $this->createMock(RouteCollection::class);
        $matchedRoute = null;
        $routeMatcher = $this->createMock(IRouteMatcher::class);
        $routeMatcher->expects($this->once())
            ->method('tryMatch')
            ->with('GET', 'uri', ['HEADER' => 'value'], $routes, $matchedRoute)
            ->willReturn(false);
        $router = new Router($routes, $routeMatcher);
        $router->route('GET', 'uri', ['HEADER' => 'value']);
    }
}
