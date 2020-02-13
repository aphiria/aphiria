<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Tests\UriMatchers\Compilers\Trees\Caching;

use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\MethodRouteAction;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\Route;
use Aphiria\Routing\UriTemplates\Compilers\Tries\Caching\FileTrieCache;
use Aphiria\Routing\UriTemplates\Compilers\Tries\LiteralTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\RootTrieNode;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

/**
 * Tests the file trie cache
 */
class FileTrieCacheTest extends TestCase
{
    /** @var string The path to the trie cache */
    private const PATH = __DIR__ . '/tmp/routes.cache';
    private FileTrieCache $cache;

    protected function setUp(): void
    {
        $this->cache = new FileTrieCache(self::PATH);
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
        \file_put_contents(self::PATH, 'foo');
        $this->assertTrue($this->cache->has());
    }

    public function testSetCreatesTheFile(): void
    {
        $this->cache->set(new RootTrieNode());
        $this->assertFileExists(self::PATH);
    }
}
