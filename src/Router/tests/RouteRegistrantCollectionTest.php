<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\Caching\IRouteCache;
use Aphiria\Routing\MethodRouteAction;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteRegistrantCollection;
use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route registrant collection
 */
class RouteRegistrantCollectionTest extends TestCase
{
    public function testAddingRegistrantCausesItToBeInvokedWhenRegisteringRoutes(): void
    {
        $registrants = new RouteRegistrantCollection();
        $singleRegistrant = new class() implements IRouteRegistrant
        {
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
        $cachedRoutes = new RouteCollection();
        $cachedRoutes->add(new Route(new UriTemplate('foo'), new MethodRouteAction('Foo', 'bar'), []));
        $cache = $this->createMock(IRouteCache::class);
        $cache->expects($this->at(0))
            ->method('get')
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
        $cache->expects($this->at(0))
            ->method('get')
            ->willReturn(null);
        $cache->expects($this->at(1))
            ->method('set')
            ->with($expectedRoutes);
        $collection = new RouteRegistrantCollection($cache);
        $collection->registerRoutes($expectedRoutes);
    }
}
