<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Caching;

use Aphiria\Routing\Caching\FileRouteCache;
use Aphiria\Routing\ClosureRouteAction;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\MethodRouteAction;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

/**
 * Tests the file route cache
 */
class FileRouteCacheTest extends TestCase
{
    /** @var string The path to the route cache */
    private const PATH = __DIR__ . '/tmp/routes.cache';
    private FileRouteCache $cache;

    protected function setUp(): void
    {
        $this->cache = new FileRouteCache(self::PATH);
    }

    protected function tearDown(): void
    {
        if (\file_exists(self::PATH)) {
            @\unlink(self::PATH);
        }
    }

    public function testFlushDeletesFile(): void
    {
        \file_put_contents(self::PATH, 'foo');
        $this->cache->flush();
        $this->assertFileNotExists(self::PATH);
    }

    public function testGetOnHitReturnsRoutesWithClosureAction(): void
    {
        // We are purposely testing setting every type of property inside the route to test that they're all unserializable
        $routes = new RouteCollection([
            new Route(
                new UriTemplate('foo'),
                new ClosureRouteAction(fn () => null),
                [$this->createMock(IRouteConstraint::class)],
                [new MiddlewareBinding('foo')]
            )
        ]);
        // We have to clone the routes because serializing them will technically alter closure/serialized closure property values
        $expectedRoutes = clone $routes;
        $this->cache->set($routes);
        $this->assertEquals($expectedRoutes, $this->cache->get());
    }

    public function testGetOnHitReturnsRoutesWithMethodAction(): void
    {
        // We are purposely testing setting every type of property inside the route to test that they're all unserializable
        $routes = new RouteCollection([
            new Route(
                new UriTemplate('foo'),
                new MethodRouteAction('Foo', 'bar'),
                [$this->createMock(IRouteConstraint::class)],
                [new MiddlewareBinding('foo')]
            )
        ]);
        $this->cache->set($routes);
        $this->assertEquals($routes, $this->cache->get());
    }

    public function testGetOnMissReturnsNull(): void
    {
        $this->assertNull($this->cache->get());
    }

    public function testHasReturnsExistenceOfFile(): void
    {
        $this->assertFalse($this->cache->has());
        \file_put_contents(self::PATH, 'foo');
        $this->assertTrue($this->cache->has());
    }

    public function testSetCreatesTheFile(): void
    {
        $this->cache->set(new RouteCollection());
        $this->assertFileExists(self::PATH);
    }
}
