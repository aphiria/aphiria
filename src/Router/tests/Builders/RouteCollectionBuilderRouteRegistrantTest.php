<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Builders;

use Aphiria\Routing\Builders\RouteCollectionBuilder;
use Aphiria\Routing\Builders\RouteCollectionBuilderRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;

class RouteCollectionBuilderRouteRegistrantTest extends TestCase
{
    public function testConstructingWithSingleCallbackInvokesItOnRegistration(): void
    {
        $callback = function (RouteCollectionBuilder $routes): void {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $routes->get('foo')
                ->mapsToMethod($controller::class, 'bar');
        };
        $registrant = new RouteCollectionBuilderRouteRegistrant($callback);
        $routes = new RouteCollection();
        $registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
    }

    public function testRegisteringRoutesInvokesCallbacksWithRouteBuilder(): void
    {
        $callback = function (RouteCollectionBuilder $routes): void {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $routes->get('foo')
                ->mapsToMethod($controller::class, 'bar');
        };
        $registrant = new RouteCollectionBuilderRouteRegistrant([$callback]);
        $routes = new RouteCollection();
        $registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
    }
}
