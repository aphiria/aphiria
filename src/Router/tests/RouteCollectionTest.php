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

use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

class RouteCollectionTest extends TestCase
{
    private RouteCollection $collection;

    protected function setUp(): void
    {
        $this->collection = new RouteCollection();
    }

    public function testCopyEffectivelyDuplicatesAnotherCollection(): void
    {
        $controller = new class() {
            public function bar(): void
            {
            }
        };
        $routes1 = new RouteCollection();
        $routes2 = new RouteCollection();
        $expectedRoute = new Route(new UriTemplate('foo'), new RouteAction($controller::class, 'bar'), [], [], 'name');
        $routes1->add($expectedRoute);
        $routes2->copy($routes1);
        $this->assertSame([$expectedRoute], $routes2->getAll());
        $this->assertSame($expectedRoute, $routes2->getNamedRoute('name'));
    }

    public function testCreatingWithRoutesAddsRoutesToCollection(): void
    {
        $controller = new class() {
            public function bar(): void
            {
            }
        };
        $expectedRoutes = [
            new Route(new UriTemplate('abc'), new RouteAction($controller::class, 'bar'), [])
        ];
        $collection = new RouteCollection($expectedRoutes);
        $this->assertEquals($expectedRoutes, $collection->getAll());
    }

    public function testGettingAllRoutesReturnsAllRegisteredRoutes(): void
    {
        $controller = new class() {
            public function bar(): void
            {
            }

            public function baz(): void
            {
            }
        };
        $expectedRoutes = [
            new Route(new UriTemplate('abc'), new RouteAction($controller::class, 'bar'), []),
            new Route(new UriTemplate('def'), new RouteAction($controller::class, 'baz'), [])
        ];
        $this->collection->addMany($expectedRoutes);
        $this->assertEquals($expectedRoutes, $this->collection->getAll());
    }

    public function testGettingNamedRouteThatDoesNotExistReturnsNull(): void
    {
        $this->assertNull($this->collection->getNamedRoute('foo'));
    }

    public function testGettingNamedRouteThatWasAddedInBulk(): void
    {
        $controller = new class() {
            public function bar(): void
            {
            }
        };
        $expectedRoute = new Route(
            new UriTemplate('abc'),
            new RouteAction($controller::class, 'bar'),
            [],
            [],
            'foo'
        );
        $this->collection->addMany([$expectedRoute]);
        $this->assertSame($expectedRoute, $this->collection->getNamedRoute('foo'));
    }

    public function testGettingNamedRouteThatWasAddedIt(): void
    {
        $controller = new class() {
            public function bar(): void
            {
            }
        };
        $expectedRoute = new Route(
            new UriTemplate('abc'),
            new RouteAction($controller::class, 'bar'),
            [],
            [],
            'foo'
        );
        $this->collection->add($expectedRoute);
        $this->assertSame($expectedRoute, $this->collection->getNamedRoute('foo'));
    }
}
