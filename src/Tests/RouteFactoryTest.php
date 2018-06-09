<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests;

use Opulence\Routing\Builders\RouteBuilderRegistry;
use Opulence\Routing\Caching\IRouteCache;
use Opulence\Routing\RouteCollection;
use Opulence\Routing\RouteFactory;

/**
 * Tests the route factory
 */
class RouteFactoryTest extends \PHPUnit\Framework\TestCase
{
    /** @var IRouteCache|\PHPUnit_Framework_MockObject_MockObject The route cache to use */
    private $routeCache;

    public function setUp(): void
    {
        $this->routeCache = $this->createMock(IRouteCache::class);
    }

    public function testCacheHitReturnsCachedRoutes(): void
    {
        $callback = function (RouteBuilderRegistry $routes) {
            // Don't do anything
        };
        $routeCollection = new RouteCollection();
        $factory = new RouteFactory($callback, $this->routeCache);
        $this->routeCache->expects($this->once())
            ->method('get')
            ->willReturn($routeCollection);
        $this->assertSame($routeCollection, $factory->createRoutes());
    }

    public function testCacheMissCallsRouteBuilderRegistryAndCachesItsResults(): void
    {
        $callback = function (RouteBuilderRegistry $routes) {
            // Don't do anything
        };
        $routeBuilderRegistry = new RouteBuilderRegistry();
        $routeCollection = $routeBuilderRegistry->buildAll();
        $factory = new RouteFactory($callback, $this->routeCache, $routeBuilderRegistry);
        $this->routeCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $this->routeCache->expects($this->once())
            ->method('set')
            ->with($routeCollection);
        $this->assertEquals($routeCollection, $factory->createRoutes());
    }

    public function testNotUsingCacheCallCallbackEveryTime(): void
    {
        $callback = function (RouteBuilderRegistry $routes) {
            // Don't do anything
        };
        $routeBuilderRegistry = new RouteBuilderRegistry();
        $routeCollection = $routeBuilderRegistry->buildAll();
        $factory = new RouteFactory($callback, null, $routeBuilderRegistry);
        $this->assertEquals($routeCollection, $factory->createRoutes());
    }
}
