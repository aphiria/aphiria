<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\Caching\IRouteCache;
use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteRegistrantCollection;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

class RouteRegistrantCollectionTest extends TestCase
{
    public function testAddingRegistrantCausesItToBeInvokedWhenRegisteringRoutes(): void
    {
        $registrants = new RouteRegistrantCollection();
        $singleRegistrant = new class () implements IRouteRegistrant {
            public bool $wasInvoked = false;

            /**
             * @inheritdoc
             */
            public function registerRoutes(RouteCollection $routes): void
            {
                $this->wasInvoked = true;
            }
        };
        $registrants->add($singleRegistrant);
        $routes = new RouteCollection();
        $registrants->registerRoutes($routes);
        $this->assertTrue($singleRegistrant->wasInvoked);
    }

    public function testCacheHitCopiesCachedConstraintsIntoParameterConstraints(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $cachedRoutes = new RouteCollection();
        $cachedRoutes->add(new Route(new UriTemplate('foo'), new RouteAction($controller::class, 'bar'), []));
        $cache = $this->createMock(IRouteCache::class);
        $cache->method('get')
            ->willReturn($cachedRoutes);
        $collection = new RouteRegistrantCollection($cache);
        $paramRoutes = new RouteCollection();
        $collection->registerRoutes($paramRoutes);
        $this->assertEquals($cachedRoutes, $paramRoutes);
    }

    public function testCacheMissPopulatesCache(): void
    {
        $expectedRoutes = new RouteCollection();
        $cache = $this->createMock(IRouteCache::class);
        $cache->method('get')
            ->willReturn(null);
        $cache->method('set')
            ->with($expectedRoutes);
        $collection = new RouteRegistrantCollection($cache);
        $collection->registerRoutes($expectedRoutes);
        // Dummy assertion
        $this->assertTrue(true);
    }
}
