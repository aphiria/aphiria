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
use Aphiria\Routing\RouteRegistrantCollection;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the cached route registrant
 */
class CachedRouteRegistrantTest extends TestCase
{
    public function testRegisteringRoutesWithCacheThatHitsReturnsThoseRoutes(): void
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
        $routeRegistrants = new RouteRegistrantCollection();
        $routeRegistrants->add($singleRegistrant);
        $cachedRegistrant = new CachedRouteRegistrant($routeCache, $routeRegistrants);
        $actualRoutes = new RouteCollection();
        $cachedRegistrant->registerRoutes($actualRoutes);
        $actualRouteArr = $actualRoutes->getAll();
        $this->assertCount(1, $actualRouteArr);
        $this->assertSame($expectedRoutes->getAll()[0], $actualRouteArr[0]);
    }

    public function testRegisteringRoutesWithCacheThatMissesStillCallsTheRegistrants(): void
    {
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
        /** @var IRouteCache|MockObject $routeCache */
        $routeCache = $this->createMock(IRouteCache::class);
        $routeCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $routeRegistrants = new RouteRegistrantCollection();
        $routeRegistrants->add($singleRegistrant);
        $routes = new RouteCollection();
        $cachedRegistrant = new CachedRouteRegistrant($routeCache, $routeRegistrants);
        $cachedRegistrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $this->assertEquals('/foo', $routeArr[0]->uriTemplate->pathTemplate);
    }

    public function testRegisteringRoutesWithCacheWillSetThemInCacheOnCacheMiss(): void
    {
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
        /** @var IRouteCache|MockObject $routeCache */
        $routeCache = $this->createMock(IRouteCache::class);
        $routeCache->expects($this->at(0))
            ->method('get')
            ->willReturn(null);
        $routeCache->expects($this->at(1))
            ->method('set')
            ->with($this->callback(function (RouteCollection $routes) {
                return \count($routes->getAll()) === 1
                    && $routes->getAll()[0]->uriTemplate->pathTemplate === '/foo';
            }));
        $routeRegistrants = new RouteRegistrantCollection();
        $routeRegistrants->add($singleRegistrant);
        $cachedRegistrant = new CachedRouteRegistrant($routeCache, $routeRegistrants);
        $routes = new RouteCollection();
        $cachedRegistrant->registerRoutes($routes);
        $routes->getAll();
    }

    public function testRegisteringRoutesWithNoRegistrantsWillReturnEmptyCollection(): void
    {
        $routeCache = $this->createMock(IRouteCache::class);
        $routeCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $cachedRegistrant = new CachedRouteRegistrant($routeCache, new RouteRegistrantCollection());
        $routes = new RouteCollection();
        $cachedRegistrant->registerRoutes($routes);
        $this->assertCount(0, $routes->getAll());
    }
}
