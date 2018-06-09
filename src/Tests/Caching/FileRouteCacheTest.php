<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Caching;

use Opulence\Routing\Caching\FileRouteCache;
use Opulence\Routing\ClosureRouteAction;
use Opulence\Routing\MethodRouteAction;
use Opulence\Routing\Middleware\MiddlewareBinding;
use Opulence\Routing\Route;
use Opulence\Routing\RouteCollection;
use Opulence\Routing\UriTemplates\UriTemplate;

/**
 * Tests the file route cache
 */
class FileRouteCacheTest extends \PHPUnit\Framework\TestCase
{
    /** @var string The path to the route cache */
    private const PATH = __DIR__ . '/tmp/routes.cache';
    /** @var FileRouteCache The cache to use in tests */
    private $cache;

    public function setUp(): void
    {
        $this->cache = new FileRouteCache(self::PATH);
    }

    public function tearDown()
    {
        if (file_exists(self::PATH)) {
            @unlink(self::PATH);
        }
    }

    public function testFlushDeletesFile(): void
    {
        file_put_contents(self::PATH, 'foo');
        $this->cache->flush();
        $this->assertFalse(file_exists(self::PATH));
    }

    public function testGetOnHitReturnsCachedRoutes(): void
    {
        $routeCollection = new RouteCollection();
        $closureRoute = new Route(
            'GET',
            new UriTemplate('regex1', false),
            new ClosureRouteAction(function () {
                // Don't do anything
            }),
            [new MiddlewareBinding('foo1', ['bar1' => 'baz1'])],
            'closureRoute',
            ['header1' => 'value1']
        );
        $methodRoute = new Route(
            'GET',
            new UriTemplate('regex2', false),
            new MethodRouteAction('class', 'method'),
            [new MiddlewareBinding('foo2', ['bar2' => 'baz2'])],
            'methodRoute',
            ['header2' => 'value2']
        );
        $routeCollection->add($closureRoute);
        $routeCollection->add($methodRoute);
        $this->cache->set($routeCollection);
        $this->assertEquals($routeCollection, $this->cache->get());
    }

    public function testGetOnMissReturnsNull(): void
    {
        $this->assertNull($this->cache->get());
    }

    public function testHasCorrectlyReturnsTheExistenceOfFile(): void
    {
        $this->assertFalse($this->cache->has());
        file_put_contents(self::PATH, 'foo');
        $this->assertTrue($this->cache->has());
    }
}
