<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\LazyRouteFactory;
use PHPUnit\Framework\TestCase;

/**
 * Tests the lazy route factory
 */
class LazyRouteFactoryTest extends TestCase
{
    public function testCreatingRoutesWillIncludeRoutesInInitialDelegate(): void
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

    public function testCreatingRoutesWillIncludeRoutesInAddedDelegate(): void
    {
        $factory = new LazyRouteFactory();
        $factory->addFactoryDelegate(function () {
            $routes = new RouteBuilderRegistry();
            $routes->map('GET', 'foo')
                ->toMethod('Foo', 'bar');

            return $routes->buildAll();
        });
        $routes = $factory->createRoutes()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testCreatingRoutesWithNoDelegatesWillReturnEmptyCollection(): void
    {
        $factory = new LazyRouteFactory();
        $this->assertCount(0, $factory->createRoutes()->getAll());
    }
}
