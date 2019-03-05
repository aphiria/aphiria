<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing\Tests\Matchers\Trees;

use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\Matchers\Trees\Caching\ITrieCache;
use Aphiria\Routing\Matchers\Trees\Compilers\ITrieCompiler;
use Aphiria\Routing\Matchers\Trees\RootTrieNode;
use Aphiria\Routing\Matchers\Trees\TrieFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the trie factory
 */
class TrieFactoryTest extends TestCase
{
    /** @var TrieFactory */
    private $trieFactory;
    /** @var RouteBuilderRegistry */
    private $routeBuilders;
    /** @var ITrieCache|MockObject */
    private $trieCache;
    /** @var ITrieCompiler|MockObject */
    private $trieCompiler;

    protected function setUp(): void
    {
        // The tests expect one route registered
        $this->routeBuilders = new RouteBuilderRegistry();
        $this->routeBuilders->map('GET', 'foo')
            ->toMethod('Bar', 'baz');
        $this->trieCache = $this->createMock(ITrieCache::class);
        $this->trieCompiler = $this->createMock(ITrieCompiler::class);
        $this->trieFactory = new TrieFactory($this->routeBuilders, $this->trieCache, $this->trieCompiler);
    }

    public function testCreatingTrieWithCacheHitReturnsTrieFromCache(): void
    {
        $expectedTrie = new RootTrieNode();
        $this->trieCache->expects($this->once())
            ->method('get')
            ->willReturn($expectedTrie);
        $this->assertSame($expectedTrie, $this->trieFactory->createTrie());
    }

    public function testCreatingTrieWithCacheMissSetsItInCache(): void
    {
        $expectedTrie = new RootTrieNode();
        $this->trieCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $this->trieCompiler->expects($this->once())
            ->method('compile')
            ->willReturn($expectedTrie);
        $this->trieCache->expects($this->once())
            ->method('set')
            ->with($expectedTrie);
        // Specifically not testing for same trie because createTrie() creates a brand new node on cache miss
        $this->assertEquals($expectedTrie, $this->trieFactory->createTrie());
    }

    public function testCreatingTrieWithNoCacheSetCreatesTrieFromCompiler(): void
    {
        $trieFactory = new TrieFactory($this->routeBuilders, null, $this->trieCompiler);
        $expectedTrie = new RootTrieNode();
        $this->trieCompiler->expects($this->once())
            ->method('compile')
            ->willReturn($expectedTrie);
        // Specifically not testing for same trie because createTrie() creates a brand new node when not using a cache
        $this->assertEquals($expectedTrie, $trieFactory->createTrie());
    }
}
