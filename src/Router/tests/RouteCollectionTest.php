<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\MethodRouteAction;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route collection
 */
class RouteCollectionTest extends TestCase
{
    private RouteCollection $collection;

    protected function setUp(): void
    {
        $this->collection = new RouteCollection();
    }

    public function testCreatingWithRoutesAddsRoutesToCollection(): void
    {
        $expectedRoutes = [
            new Route(new UriTemplate('abc'), new MethodRouteAction('Foo', 'bar'), [])
        ];
        $collection = new RouteCollection($expectedRoutes);
        $this->assertEquals($expectedRoutes, $collection->getAll());
    }

    public function testGettingAllRoutesReturnsAllRegisteredRoutes(): void
    {
        $expectedRoutes = [
            new Route(new UriTemplate('abc'), new MethodRouteAction('Foo', 'bar'), []),
            new Route(new UriTemplate('def'), new MethodRouteAction('Foo', 'baz'), [])
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
            new MethodRouteAction('Foo', 'bar'),
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
            new MethodRouteAction('Foo', 'bar'),
            [],
            [],
            'foo'
        );
        $this->collection->add($expectedRoute);
        $this->assertSame($expectedRoute, $this->collection->getNamedRoute('foo'));
    }
}
