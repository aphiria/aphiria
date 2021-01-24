<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Caching;

use Aphiria\Routing\Caching\FileRouteCache;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;
use RuntimeException;

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
        $this->assertFileDoesNotExist(self::PATH);
    }

    public function testGetOnHitReturnsRoutesWithAction(): void
    {
        $controller = new class() {
            public function bar(): void
            {
            }
        };
        $middleware = new class() {
        };
        // We are purposely testing setting every type of property inside the route to test that they're all unserializable
        $routes = new RouteCollection([
            new Route(
                new UriTemplate('foo'),
                new RouteAction($controller::class, 'bar'),
                [$this->createMock(IRouteConstraint::class)],
                [new MiddlewareBinding($middleware::class)]
            )
        ]);
        $this->cache->set($routes);
        $this->assertEquals($routes, $this->cache->get());
    }

    public function testGetOnMissReturnsNull(): void
    {
        $this->assertNull($this->cache->get());
    }

    public function testGettingFromCacheWithInvalidCachedDataThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Routes must be instance of ' . RouteCollection::class . ' or null');
        \file_put_contents(self::PATH, '');
        $this->cache->get();
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
