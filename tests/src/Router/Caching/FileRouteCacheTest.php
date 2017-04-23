<?php
namespace Opulence\Router\Caching;

use Opulence\Router\ClosureRouteAction;
use Opulence\Router\MethodRouteAction;
use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\Route;
use Opulence\Router\RouteCollection;
use Opulence\Router\UriTemplates\UriTemplate;

/**
 * Tests the file route cache
 */
class FileRouteCacheTest extends \PHPUnit\Framework\TestCase
{
    /** @var string The path to the route cache */
    private const PATH = __DIR__ . '/tmp/routes.cache';
    /** @var FileRouteCache The cache to use in tests */
    private $cache = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->cache = new FileRouteCache(self::PATH);
    }

    /**
     * Tears down the tests
     */
    public function tearDown()
    {
        if (file_exists(self::PATH)) {
            @unlink(self::PATH);
        }
    }

    /**
     * Tests that flushing deletes the file
     */
    public function testFlushDeletesFile() : void
    {
        file_put_contents(self::PATH, 'foo');
        $this->cache->flush();
        $this->assertFalse(file_exists(self::PATH));
    }

    /**
     * Tests that getting the routes on a hit returns cached routes
     */
    public function testGetOnHitReturnsCachedRoutes() : void
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

    /**
     * Tests that getting the routes on a miss returns null
     */
    public function testGetOnMissReturnsNull() : void
    {
        $this->assertNull($this->cache->get());
    }

    /**
     * Tests that checking for the existence of a cached routes file returns the correct value
     */
    public function testHasCorrectlyReturnsTheExistenceOfFile(): void
    {
        $this->assertFalse($this->cache->has());
        file_put_contents(self::PATH, 'foo');
        $this->assertTrue($this->cache->has());
    }
}
