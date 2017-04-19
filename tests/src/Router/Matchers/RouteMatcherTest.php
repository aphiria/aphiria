<?php
namespace Opulence\Router\Matchers;

use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\Route;
use Opulence\Router\RouteAction;
use Opulence\Router\RouteCollection;
use Opulence\Router\UriTemplates\IUriTemplate;

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
    /*public function setUp() : void
    {
        $this->matcher = new RouteMatcher();
        $this->routeCollection = $this->createMock(RouteCollection::class);
    }*/

    /**
     * Tests that the header names are case-insensitive
     */
    /*public function testHeaderNamesAreCaseInsensitive() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                $this->createUriTemplateMock('uri', false),
                $this->createMock(RouteAction::class)
            ),
            new Route(
                ['GET'],
                $this->createUriTemplateMock('uri', true),
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
            $this->matcher->tryMatch('GET', 'uri', ['HEADER' => 'value'], $this->routeCollection, $matchedRoute)
        );
        $this->assertSame($expectedRoutes[1]->getAction(), $matchedRoute->getAction());
        $this->assertSame($expectedRoutes[1]->getMiddlewareBindings(), $matchedRoute->getMiddlewareBindings());
    }*/

    /**
     * Tests a matching URI but no matching header returns no matches
     */
    /*public function testMatchingUriButNoMatchingHeaderReturnsNoMatches() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                $this->createUriTemplateMock('uri', true),
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
            $this->matcher->tryMatch('GET', 'uri', ['HEADER' => 'right'], $this->routeCollection, $matchedRoute)
        );
    }*/

    /**
     * Tests a matching URI with headers to match returns a match
     */
    /*public function testMatchingUriWithHeadersToMatchReturnsMatch() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                $this->createUriTemplateMock('uri', true),
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
            $this->matcher->tryMatch('GET', 'uri', ['HEADER' => 'right'], $this->routeCollection, $matchedRoute)
        );
        $this->assertSame($expectedRoutes[0]->getAction(), $matchedRoute->getAction());
        $this->assertSame($expectedRoutes[0]->getMiddlewareBindings(), $matchedRoute->getMiddlewareBindings());
    }*/

    /**
     * Tests a matching URI with no headers to match returns a match
     */
    /*public function testMatchingUriWithNoHeadersToMatchReturnsMatch() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                $this->createUriTemplateMock('uri', true),
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
            $this->matcher->tryMatch('GET', 'uri', ['HEADER' => 'value'], $this->routeCollection, $matchedRoute)
        );
        $this->assertSame($expectedRoutes[0]->getAction(), $matchedRoute->getAction());
        $this->assertSame($expectedRoutes[0]->getMiddlewareBindings(), $matchedRoute->getMiddlewareBindings());
    }*/

    /**
     * Tests no match for a URI returns no matches
     */
    /*public function testNoMatchForUriReturnsNoMatches() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                $this->createUriTemplateMock('uri', false),
                $this->createMock(RouteAction::class)
            )
        ];
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $matchedRoute = null;
        $this->assertFalse(
            $this->matcher->tryMatch('GET', 'uri', ['HEADER' => 'value'], $this->routeCollection, $matchedRoute)
        );
    }*/

    /**
     * Creates a URI template mock
     *
     * @param string $uri The URI to try to match on
     * @param bool $shouldMatch Whether or not the URI template should match
     * @return IUriTemplate|\PHPUnit_Framework_MockObject_MockObject The mock URI template
     */
    private function createUriTemplateMock(string $uri, bool $shouldMatch) : IUriTemplate
    {
        $uriTemplate = $this->createMock(IUriTemplate::class);
        $routeVars = [];
        $uriTemplate->expects($this->once())
            ->method('tryMatch')
            ->with($uri, $routeVars)
            ->willReturn($shouldMatch);

        return $uriTemplate;
    }
}
