<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers;

use Opulence\Routing\Matchers\Constraints\IRouteConstraint;
use Opulence\Routing\Matchers\UriTemplates\UriTemplate;

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
        $this->routeCollection = $this->createMock(RouteCollection::class);
        $this->matcher = new RouteMatcher($this->routeCollection);
    }

    /**
     * Tests that a matching route with a failed constraint causes the matcher to throw an exception
     */
    public function testFailedConstraintThrowsException() : void
    {
        $this->expectException(RouteNotFoundException::class);
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo$', false),
                $this->createMock(RouteAction::class)
            )
        ];
        $constraint = $this->createMock(IRouteConstraint::class);
        $constraint->expects($this->once())
            ->method('isMatch')
            ->with('', 'foo', ['foo' => 'bar'], $expectedRoutes[0])
            ->willReturn(false);
        $matcher = new RouteMatcher($this->routeCollection, [$constraint]);
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $matcher->match('GET', '', 'foo', ['foo' => 'bar'], $this->routeCollection);
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

    /**
     * Tests that a passing constraint does not filter a route
     */
    public function testPassingConstraintDoesNotFilterRoute() : void
    {
        $expectedRoutes = [
            new Route(
                ['GET'],
                new UriTemplate('^foo$', false),
                $this->createMock(RouteAction::class)
            )
        ];
        $constraint = $this->createMock(IRouteConstraint::class);
        $constraint->expects($this->once())
            ->method('isMatch')
            ->with('', 'foo', ['foo' => 'bar'], $expectedRoutes[0])
            ->willReturn(true);
        $matcher = new RouteMatcher($this->routeCollection, [$constraint]);
        $this->routeCollection->expects($this->once())
            ->method('getByMethod')
            ->with('GET')
            ->willReturn($expectedRoutes);
        $matchedRoute = $matcher->match('GET', '', 'foo', ['foo' => 'bar'], $this->routeCollection);
        $this->assertSame($expectedRoutes[0]->getAction(), $matchedRoute->getAction());
    }
}
