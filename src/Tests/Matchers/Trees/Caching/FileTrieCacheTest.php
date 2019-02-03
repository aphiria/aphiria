<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/Aphiria/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Tests\Matchers\Trees\Caching;

use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Matchers\Trees\Caching\FileTrieCache;
use Aphiria\Routing\Matchers\Trees\LiteralTrieNode;
use Aphiria\Routing\Matchers\Trees\RootTrieNode;
use Aphiria\Routing\MethodRouteAction;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\Route;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

/**
 * Tests the file trie cache
 */
class FileTrieCacheTest extends TestCase
{
    /** @var string The path to the route cache */
    private const PATH = __DIR__ . '/tmp/routes.cache';
    /** @var FileTrieCache The cache to test */
    private $cache;

    public function setUp(): void
    {
        $this->cache = new FileTrieCache(self::PATH);
    }

    public function tearDown(): void
    {
        if (file_exists(self::PATH)) {
            @unlink(self::PATH);
        }
    }

    public function testFlushDeletesFile(): void
    {
        file_put_contents(self::PATH, 'foo');
        $this->cache->flush();
        $this->assertFileNotExists(self::PATH);
    }

    public function testGetOnHitReturnsTrieAndIncludesRoutesWithAllPropertiesSet(): void
    {
        // We are purposely testing setting every type of property inside the route to test that they're all unserializable
        $route = new Route(
            new UriTemplate('foo'),
            new MethodRouteAction('Foo', 'bar'),
            [$this->createMock(IRouteConstraint::class)],
            [new MiddlewareBinding('foo')]
        );
        $trie = new RootTrieNode([
            new LiteralTrieNode(
                'foo',
                [],
                $route
            )
        ]);
        $this->cache->set($trie);
        $this->assertEquals($trie, $this->cache->get());
    }

    public function testGetOnMissReturnsNull(): void
    {
        $this->assertNull($this->cache->get());
    }

    public function testHasReturnsExistenceOfFile(): void
    {
        $this->assertFalse($this->cache->has());
        file_put_contents(self::PATH, 'foo');
        $this->assertTrue($this->cache->has());
    }

    public function testSetCreatesTheFile(): void
    {
        $this->cache->set(new RootTrieNode());
        $this->assertFileExists(self::PATH);
    }
}
