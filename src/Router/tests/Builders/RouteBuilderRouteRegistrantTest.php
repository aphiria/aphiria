<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Builders;

use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\Builders\RouteBuilderRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use Closure;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route builder route registrant
 */
class RouteBuilderRouteRegistrantTest extends TestCase
{
    public function testRegisteringRoutesInvokesCallbacksWithRouteBuilder(): void
    {
        $callback = function (RouteBuilderRegistry $routeBuilders) {
            $routeBuilders->get('foo')
                ->toMethod('foo', 'bar');
        };
        $registrant = new RouteBuilderRouteRegistrant([$callback]);
        $routes = new RouteCollection();
        $registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
    }

    public function testConstructingWithInvalidCallbackThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Callbacks must be an instance of ' . Closure::class . ' or an array of Closures');
        new RouteBuilderRouteRegistrant(123);
    }
}
