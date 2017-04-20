<?php
namespace Opulence\Router\Matchers;

use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\Route;
use Opulence\Router\RouteAction;
use Opulence\Router\RouteCollection;
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
                new UriTemplate('^foo$', 0, false),
                $this->createMock(RouteAction::class)
            ),
            new Route(
                ['GET'],
                new UriTemplate('^bar$', 0, false),
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
        $matchedRoute = null;
        $this->assertTrue(
            $this->matcher->tryMatch('GET', '', 'bar', ['HEADER' => 'value'], $this->routeCollection, $matchedRoute)
        );
        $this->assertSame($expectedRoutes[1]->getAction(), $matchedRoute->getAction());
        $this->assertSame($expectedRoutes[1]->getMiddlewareBindings(), $matchedRoute->getMiddlewareBindings());
    }

    /**
     * Tests a matching URI but no matching header returns no matches
     */
    public function testMatchingUriButNoMatchingHeaderReturnsNoMatches() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo$', 0, false),
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
        $matchedRoute = null;
        $this->assertFalse(
            $this->matcher->tryMatch('GET', '', 'foo', ['HEADER' => 'right'], $this->routeCollection, $matchedRoute)
        );
    }

    /**
     * Tests a matching URI with headers to match returns a match
     */
    public function testMatchingUriWithHeadersToMatchReturnsMatch() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo$', 0, false),
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
        $matchedRoute = null;
        $this->assertTrue(
            $this->matcher->tryMatch('GET', '', 'foo', ['HEADER' => 'right'], $this->routeCollection, $matchedRoute)
        );
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
                new UriTemplate('^foo$', 0, false),
                $this->createMock(RouteAction::class),
                [new MiddlewareBinding('foo')]
            )
        ];
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $matchedRoute = null;
        $this->assertTrue(
            $this->matcher->tryMatch('GET', '', 'foo', ['HEADER' => 'value'], $this->routeCollection, $matchedRoute)
        );
        $this->assertSame($expectedRoutes[0]->getAction(), $matchedRoute->getAction());
        $this->assertSame($expectedRoutes[0]->getMiddlewareBindings(), $matchedRoute->getMiddlewareBindings());
    }

    /**
     * Tests no match for a URI returns no matches
     */
    public function testNoMatchForUriReturnsNoMatches() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo$', 0, false),
                $this->createMock(RouteAction::class)
            )
        ];
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $matchedRoute = null;
        $this->assertFalse(
            $this->matcher->tryMatch('GET', '', 'bar', [], $this->routeCollection, $matchedRoute)
        );
    }
    
    /**
     * Tests that matching can occur on URI templates with differing numbers of capturing groups
     */
    public function testMatchingCanOccurOnUriTemplatesWithDifferingNumbersOfCapturingGroups() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo(?P<var1>1)$', 1, false),
                $this->createMock(RouteAction::class)
            ),
            new Route(
                ['GET'],
                new UriTemplate('^bar(?P<var2>2)(?P<var3>3)(?P<var4>4)$', 3, false),
                $this->createMock(RouteAction::class)
            ),
            new Route(
                ['GET'],
                new UriTemplate('^baz(?P<var5>5)(?P<var6>6)$', 2, false),
                $this->createMock(RouteAction::class)
            )
        ];
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $matchingPaths = [
            'foo1' => ['var1' => '1'],
            'bar234' => ['var2' => '2', 'var3' => '3', 'var4' => '4'],
            'baz56' => ['var5' => '5', 'var6' => '6']
        ];
        
        foreach ($matchingPaths as $matchingPath => $expectedRouteVars) {
            $matchedRoute = null;
            $this->assertTrue(
                $this->matcher->tryMatch('GET', '', $matchingPath, [], $this->routeCollection, $matchedRoute)
            );
            $this->assertEquals($expectedRouteVars, $matchedRoute->getRouteVars());
        }
    }
}
