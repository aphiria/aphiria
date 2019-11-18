<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Caching;

use Aphiria\Routing\Caching\IRouteCache;
use Aphiria\Routing\Caching\CachedRouteRegistrant;
use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\MethodRouteAction;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the cached route registrant
 */
class CachedRouteRegistrantTest extends TestCase
{
    public function testCreatingRoutesWillIncludeRoutesInInitialRegistrant(): void
    {
        $routeCache = $this->createMock(IRouteCache::class);
        $routeCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $singleRegistrant = new class() implements IRouteRegistrant
        {
            /**
             * @inheritdoc
             */
            public function registerRoutes(RouteCollection $routes): void
            {
                $routes->add(new Route(new UriTemplate('foo'), new MethodRouteAction('Foo', 'bar'), []));
            }
        };
        $cachedRegistrant = new CachedRouteRegistrant($routeCache, $singleRegistrant);
        $routes = new RouteCollection();
        $cachedRegistrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $this->assertEquals('/foo', $routeArr[0]->uriTemplate->pathTemplate);
    }

    public function testCreatingRoutesWillIncludeRoutesInAddedRegistrant(): void
    {
        $routeCache = $this->createMock(IRouteCache::class);
        $routeCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $singleRegistrant = new class() implements IRouteRegistrant
        {
            /**
             * @inheritdoc
             */
            public function registerRoutes(RouteCollection $routes): void
            {
                $routes->add(new Route(new UriTemplate('foo'), new MethodRouteAction('Foo', 'bar'), []));
            }
        };
        $cachedRegistrant = new CachedRouteRegistrant($routeCache);
        $cachedRegistrant->addRouteRegistrant($singleRegistrant);
        $routes = new RouteCollection();
        $cachedRegistrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $this->assertEquals('/foo', $routeArr[0]->uriTemplate->pathTemplate);
    }

    public function testCreatingRoutesWithCacheThatHitsReturnsThoseRoutes(): void
    {
        /** @var IRouteCache|MockObject $routeCache */
        $expectedRoutes = new RouteCollection();
        $expectedRoutes->add(new Route(new UriTemplate('foo'), new MethodRouteAction('foo', 'bar'), []));
        $routeCache = $this->createMock(IRouteCache::class);
        $routeCache->expects($this->once())
            ->method('get')
            ->willReturn($expectedRoutes);
        $singleRegistrant = new class() implements IRouteRegistrant
        {
            /**
             * @inheritdoc
             */
            public function registerRoutes(RouteCollection $routes): void
            {
                $routes->add(new Route(new UriTemplate('foo'), new MethodRouteAction('Foo', 'bar'), []));
            }
        };
        $cachedRegistrant = new CachedRouteRegistrant($routeCache);
        $cachedRegistrant->addRouteRegistrant($singleRegistrant);
        $actualRoutes = new RouteCollection();
        $cachedRegistrant->registerRoutes($actualRoutes);
        $actualRouteArr = $actualRoutes->getAll();
        $this->assertCount(1, $actualRouteArr);
        $this->assertSame($expectedRoutes->getAll()[0], $actualRouteArr[0]);
    }

    public function testCreatingRoutesWithCacheThatMissesStillCallsTheRegistrants(): void
    {
        /** @var IRouteCache|MockObject $routeCache */
        $routeCache = $this->createMock(IRouteCache::class);
        $cachedRegistrant = new CachedRouteRegistrant($routeCache);
        $singleRegistrant = new class() implements IRouteRegistrant
        {
            /**
             * @inheritdoc
             */
            public function registerRoutes(RouteCollection $routes): void
            {
                $routes->add(new Route(new UriTemplate('foo'), new MethodRouteAction('Foo', 'bar'), []));
            }
        };
        $cachedRegistrant->addRouteRegistrant($singleRegistrant);
        $routeCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $routes = new RouteCollection();
        $cachedRegistrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $this->assertEquals('/foo', $routeArr[0]->uriTemplate->pathTemplate);
    }

    public function testCreatingRoutesWithCacheWillSetThemInCacheOnCacheMiss(): void
    {
        /** @var IRouteCache|MockObject $routeCache */
        $routeCache = $this->createMock(IRouteCache::class);
        $cachedRegistrant = new CachedRouteRegistrant($routeCache);
        $singleRegistrant = new class() implements IRouteRegistrant
        {
            /**
             * @inheritdoc
             */
            public function registerRoutes(RouteCollection $routes): void
            {
                $routes->add(new Route(new UriTemplate('foo'), new MethodRouteAction('Foo', 'bar'), []));
            }
        };
        $cachedRegistrant->addRouteRegistrant($singleRegistrant);
        $routeCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $routeCache->expects($this->once())
            ->method('set')
            ->with($this->callback(function (RouteCollection $routes) {
                return \count($routes->getAll()) === 1
                    && $routes->getAll()[0]->uriTemplate->pathTemplate === '/foo';
            }));
        $routes = new RouteCollection();
        $cachedRegistrant->registerRoutes($routes);
        $routes->getAll();
    }

    public function testCreatingRoutesWithNoRegistrantsWillReturnEmptyCollection(): void
    {
        $routeCache = $this->createMock(IRouteCache::class);
        $routeCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $cachedRegistrant = new CachedRouteRegistrant($routeCache);
        $routes = new RouteCollection();
        $cachedRegistrant->registerRoutes($routes);
        $this->assertCount(0, $routes->getAll());
    }
}
