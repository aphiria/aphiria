<?php
namespace Opulence\Router\Matchers;

use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\Route;
use Opulence\Router\RouteAction;
use Opulence\Router\RouteCollection;
use Opulence\Router\RouteNotFoundException;
use Opulence\Router\UriTemplates\UriTemplate;

/**
 * Tests the route matcher
 */
class RouteMatcherTest extends \PHPUnit\Framework\TestCase
{
    /** @var RouteMatcher The matcher to use in tests */
    private $matcher = null;
    /** @var RouteCollection|\PHPUnit_Framework_MockObject_MockObject The route collection to use in tests */
    private $routeCollection = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->matcher = new RouteMatcher();
        $this->routeCollection = $this->createMock(RouteCollection::class);
    }

    /**
     * Tests that the header names are case-insensitive
     */
    public function testHeaderNamesAreCaseInsensitive() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo$', false),
                $this->createMock(RouteAction::class)
            ),
            new Route(
                ['GET'],
                new UriTemplate('^bar$', false),
                $this->createMock(RouteAction::class),
                [new MiddlewareBinding('foo')],
                null,
                ['header' => 'value']
            )
        ];
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $matchedRoute = $this->matcher->match('GET', '', 'bar', ['HEADER' => 'value'], $this->routeCollection);
        $this->assertSame($expectedRoutes[1]->getAction(), $matchedRoute->getAction());
        $this->assertSame($expectedRoutes[1]->getMiddlewareBindings(), $matchedRoute->getMiddlewareBindings());
    }

    /**
     * Tests that matching can occur on URI templates with differing numbers of capturing groups
     */
    public function testMatchingCanOccurOnUriTemplatesWithDifferingNumbersOfCapturingGroups() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo(1)$', false, ['var1']),
                $this->createMock(RouteAction::class)
            ),
            new Route(
                ['GET'],
                new UriTemplate('^bar(2)(3)(4)$', false, ['var2', 'var3', 'var4']),
                $this->createMock(RouteAction::class)
            ),
            new Route(
                ['GET'],
                new UriTemplate('^baz(5)(6)$', false, ['var5', 'var6']),
                $this->createMock(RouteAction::class)
            )
        ];
        $this->routeCollection->expects($this->exactly(3))
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $matchingPaths = [
            'foo1' => ['var1' => '1'],
            'bar234' => ['var2' => '2', 'var3' => '3', 'var4' => '4'],
            'baz56' => ['var5' => '5', 'var6' => '6']
        ];

        foreach ($matchingPaths as $matchingPath => $expectedRouteVars) {
            $matchedRoute = $this->matcher->match('GET', '', $matchingPath, [], $this->routeCollection);
            $this->assertEquals($expectedRouteVars, $matchedRoute->getRouteVars());
        }
    }

    /**
     * Tests a matching route with vars after a route that has no vars
     */
    public function testMatchingRouteWithVarsThatIsCheckedAfterMissedRouteWithNoVars() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo$', false),
                $this->createMock(RouteAction::class)
            ),
            new Route(
                ['GET'],
                new UriTemplate('^bar(1)(2)$', false),
                $this->createMock(RouteAction::class)
            )
        ];
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $matchedRoute = $this->matcher->match('GET', '', 'bar12', [], $this->routeCollection);
        $this->assertSame($expectedRoutes[1]->getAction(), $matchedRoute->getAction());
    }

    /**
     * Tests a matching URI but no matching header throws an exception
     */
    public function testMatchingUriButNoMatchingHeaderThrowsException() : void
    {
        $this->expectException(RouteNotFoundException::class);
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo$', false),
                $this->createMock(RouteAction::class),
                [new MiddlewareBinding('foo')],
                null,
                ['HEADER' => 'wrong']
            )
        ];
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $this->matcher->match('GET', '', 'foo', ['HEADER' => 'right'], $this->routeCollection);
    }

    /**
     * Tests a matching URI with headers to match returns a match
     */
    public function testMatchingUriWithHeadersToMatchReturnsMatch() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo$', false),
                $this->createMock(RouteAction::class),
                [new MiddlewareBinding('foo')],
                null,
                ['HEADER' => 'right']
            )
        ];
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $matchedRoute = $this->matcher->match('GET', '', 'foo', ['HEADER' => 'right'], $this->routeCollection);
        $this->assertSame($expectedRoutes[0]->getAction(), $matchedRoute->getAction());
        $this->assertSame($expectedRoutes[0]->getMiddlewareBindings(), $matchedRoute->getMiddlewareBindings());
    }

    /**
     * Tests a matching URI with no headers to match returns a match
     */
    public function testMatchingUriWithNoHeadersToMatchReturnsMatch() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo$', false),
                $this->createMock(RouteAction::class),
                [new MiddlewareBinding('foo')]
            )
        ];
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $matchedRoute = $this->matcher->match('GET', '', 'foo', ['HEADER' => 'value'], $this->routeCollection);
        $this->assertSame($expectedRoutes[0]->getAction(), $matchedRoute->getAction());
        $this->assertSame($expectedRoutes[0]->getMiddlewareBindings(), $matchedRoute->getMiddlewareBindings());
    }

    /**
     * Tests no match for a URI throws an exception
     */
    public function testNoMatchForUriThrowsException() : void
    {
        $this->expectException(RouteNotFoundException::class);
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo$', false),
                $this->createMock(RouteAction::class)
            )
        ];
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $this->matcher->match('GET', '', 'bar', [], $this->routeCollection);
    }
}
