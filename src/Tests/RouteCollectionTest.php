<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests;

use Opulence\Routing\MethodRouteAction;
use Opulence\Routing\Route;
use Opulence\Routing\RouteCollection;
use Opulence\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route collection
 */
class RouteCollectionTest extends TestCase
{
    /** @var RouteCollection */
    private $collection;

    public function setUp(): void
    {
        $this->collection = new RouteCollection();
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
