<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests;

use Opulence\Routing\Builders\RouteBuilderRegistry;
use Opulence\Routing\RouteFactory;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route factory
 */
class RouteFactoryTest extends TestCase
{
    public function testCreatingRoutesRunsCallbackAndBuildsRegistry(): void
    {
        $callback = function (RouteBuilderRegistry $routes) {
            $routes->map('GET', 'foo')
                ->toMethod('Foo', 'bar');
        };
        $routeBuilderRegistry = new RouteBuilderRegistry();
        $factory = new RouteFactory($callback, $routeBuilderRegistry);
        $routeCollection = $factory->createRoutes();
        $this->assertCount(1, $routeCollection->getAll());
        $this->assertEquals('Foo', $routeCollection->getAll()[0]->action->className);
        $this->assertEquals('bar', $routeCollection->getAll()[0]->action->methodName);
    }
}
