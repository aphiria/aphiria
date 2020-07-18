<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Builders;

use Aphiria\Routing\Builders\RouteCollectionBuilder;
use Aphiria\Routing\Builders\RouteCollectionBuilderRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use Closure;
use PHPUnit\Framework\TestCase;

class RouteCollectionBuilderRouteRegistrantTest extends TestCase
{
    public function testConstructingWithInvalidCallbackThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Closures must be an instance of ' . Closure::class . ' or an array of Closures');
        new RouteCollectionBuilderRouteRegistrant(123);
    }

    public function testConstructingWithSingleCallbackInvokesItOnRegistration(): void
    {
        $callback = function (RouteCollectionBuilder $routes) {
            $routes->get('foo')
                ->mapsToMethod('foo', 'bar');
        };
        $registrant = new RouteCollectionBuilderRouteRegistrant($callback);
        $routes = new RouteCollection();
        $registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
    }

    public function testRegisteringRoutesInvokesCallbacksWithRouteBuilder(): void
    {
        $callback = function (RouteCollectionBuilder $routes) {
            $routes->get('foo')
                ->mapsToMethod('foo', 'bar');
        };
        $registrant = new RouteCollectionBuilderRouteRegistrant([$callback]);
        $routes = new RouteCollection();
        $registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
    }
}
