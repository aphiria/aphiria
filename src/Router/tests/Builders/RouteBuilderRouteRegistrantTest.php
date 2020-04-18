<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Builders;

use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\Builders\RouteBuilderRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use Closure;
use PHPUnit\Framework\TestCase;

class RouteBuilderRouteRegistrantTest extends TestCase
{
    public function testConstructingWithInvalidCallbackThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Closures must be an instance of ' . Closure::class . ' or an array of Closures');
        new RouteBuilderRouteRegistrant(123);
    }

    public function testConstructingWithSingleCallbackInvokesItOnRegistration(): void
    {
        $callback = function (RouteBuilderRegistry $routeBuilders) {
            $routeBuilders->get('foo')
                ->mapsToMethod('foo', 'bar');
        };
        $registrant = new RouteBuilderRouteRegistrant($callback);
        $routes = new RouteCollection();
        $registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
    }

    public function testRegisteringRoutesInvokesCallbacksWithRouteBuilder(): void
    {
        $callback = function (RouteBuilderRegistry $routeBuilders) {
            $routeBuilders->get('foo')
                ->mapsToMethod('foo', 'bar');
        };
        $registrant = new RouteBuilderRouteRegistrant([$callback]);
        $routes = new RouteCollection();
        $registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
    }
}
