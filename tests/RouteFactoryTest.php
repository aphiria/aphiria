<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\RouteFactory;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route factory
 */
class RouteFactoryTest extends TestCase
{
    public function testCreatingRoutesBuildsRegistry(): void
    {
        $routeBuilders = new RouteBuilderRegistry();
        $routeBuilders->map('GET', 'foo')
            ->toMethod('Foo', 'bar');
        $factory = new RouteFactory($routeBuilders);
        $routeCollection = $factory->createRoutes();
        $this->assertCount(1, $routeCollection->getAll());
        $this->assertEquals('Foo', $routeCollection->getAll()[0]->action->className);
        $this->assertEquals('bar', $routeCollection->getAll()[0]->action->methodName);
    }
}
