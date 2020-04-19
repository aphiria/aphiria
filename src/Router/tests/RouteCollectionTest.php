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
        $routes1 = new RouteCollection();
        $routes2 = new RouteCollection();
        $expectedRoute = new Route(new UriTemplate('foo'), new RouteAction('Foo', 'bar'), [], [], 'name');
        $routes1->add($expectedRoute);
        $routes2->copy($routes1);
        $this->assertSame([$expectedRoute], $routes2->getAll());
        $this->assertSame($expectedRoute, $routes2->getNamedRoute('name'));
    }

    public function testCreatingWithRoutesAddsRoutesToCollection(): void
    {
        $expectedRoutes = [
            new Route(new UriTemplate('abc'), new RouteAction('Foo', 'bar'), [])
        ];
        $collection = new RouteCollection($expectedRoutes);
        $this->assertEquals($expectedRoutes, $collection->getAll());
    }

    public function testGettingAllRoutesReturnsAllRegisteredRoutes(): void
    {
        $expectedRoutes = [
            new Route(new UriTemplate('abc'), new RouteAction('Foo', 'bar'), []),
            new Route(new UriTemplate('def'), new RouteAction('Foo', 'baz'), [])
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
        $expectedRoute = new Route(
            new UriTemplate('abc'),
            new RouteAction('Foo', 'bar'),
            [],
            [],
            'foo'
        );
        $this->collection->addMany([$expectedRoute]);
        $this->assertSame($expectedRoute, $this->collection->getNamedRoute('foo'));
    }

    public function testGettingNamedRouteThatWasAddedIt(): void
    {
        $expectedRoute = new Route(
            new UriTemplate('abc'),
            new RouteAction('Foo', 'bar'),
            [],
            [],
            'foo'
        );
        $this->collection->add($expectedRoute);
        $this->assertSame($expectedRoute, $this->collection->getNamedRoute('foo'));
    }
}
