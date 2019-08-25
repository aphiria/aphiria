<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\Caching\IRouteCache;
use Aphiria\Routing\LazyRouteFactory;
use Aphiria\Routing\RouteCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the lazy route factory
 */
class LazyRouteFactoryTest extends TestCase
{
    public function testCreatingRoutesWillIncludeRoutesInInitialFactory(): void
    {
        $factory = new LazyRouteFactory(function () {
            $routes = new RouteBuilderRegistry();
            $routes->map('GET', 'foo')
                ->toMethod('Foo', 'bar');

            return $routes->buildAll();
        });
        $routes = $factory->createRoutes()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testCreatingRoutesWillIncludeRoutesInAddedFactory(): void
    {
        $factory = new LazyRouteFactory();
        $factory->addFactory(function () {
            $routes = new RouteBuilderRegistry();
            $routes->map('GET', 'foo')
                ->toMethod('Foo', 'bar');

            return $routes->buildAll();
        });
        $routes = $factory->createRoutes()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testCreatingRoutesWithCacheThatHitsReturnsThoseRoutes(): void
    {
        /** @var IRouteCache|MockObject $routeCache */
        $expectedRoutes = new RouteCollection();
        $routeCache = $this->createMock(IRouteCache::class);
        $routeCache->expects($this->once())
            ->method('get')
            ->willReturn($expectedRoutes);
        $factory = new LazyRouteFactory(null, $routeCache);
        $factory->addFactory(function () {
            $routes = new RouteBuilderRegistry();
            $routes->map('GET', 'foo')
                ->toMethod('Foo', 'bar');

            return $routes->buildAll();
        });
        $this->assertSame($expectedRoutes, $factory->createRoutes());
    }

    public function testCreatingRoutesWithCacheThatMissesStillRunsTheFactories(): void
    {
        /** @var IRouteCache|MockObject $routeCache */
        $routeCache = $this->createMock(IRouteCache::class);
        $factory = new LazyRouteFactory(null, $routeCache);
        $factory->addFactory(function () {
            $routes = new RouteBuilderRegistry();
            $routes->map('GET', 'foo')
                ->toMethod('Foo', 'bar');

            return $routes->buildAll();
        });
        $routeCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $routes = $factory->createRoutes()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testCreatingRoutesWithCacheWillSetThemInCacheOnCacheMiss(): void
    {
        /** @var IRouteCache|MockObject $routeCache */
        $routeCache = $this->createMock(IRouteCache::class);
        $factory = new LazyRouteFactory(null, $routeCache);
        $factory->addFactory(function () {
            $routes = new RouteBuilderRegistry();
            $routes->map('GET', 'foo')
                ->toMethod('Foo', 'bar');

            return $routes->buildAll();
        });
        $routeCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $routeCache->expects($this->once())
            ->method('set')
            ->with($this->callback(function (RouteCollection $routes) {
                return \count($routes->getAll()) === 1
                    && $routes->getAll()[0]->uriTemplate->pathTemplate === '/foo';
            }));
        $factory->createRoutes()->getAll();
    }

    public function testCreatingRoutesWithNoFactoriesWillReturnEmptyCollection(): void
    {
        $factory = new LazyRouteFactory();
        $this->assertCount(0, $factory->createRoutes()->getAll());
    }
}
