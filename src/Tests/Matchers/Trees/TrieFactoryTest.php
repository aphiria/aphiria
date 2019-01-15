<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers\Trees;

use Opulence\Routing\Matchers\Trees\Caching\ITrieCache;
use Opulence\Routing\Matchers\Trees\Compilers\ITrieCompiler;
use Opulence\Routing\Matchers\Trees\RootTrieNode;
use Opulence\Routing\Matchers\Trees\TrieFactory;
use Opulence\Routing\Route;
use Opulence\Routing\RouteCollection;
use Opulence\Routing\RouteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the trie factory
 */
class TrieFactoryTest extends TestCase
{
    /** @var TrieFactory */
    private $trieFactory;
    /** @var RouteFactory|MockObject */
    private $routeFactory;
    /** @var ITrieCache|MockObject */
    private $trieCache;
    /** @var ITrieCompiler|MockObject */
    private $trieCompiler;

    public function setUp(): void
    {
        $this->routeFactory = $this->createMock(RouteFactory::class);
        $this->trieCache = $this->createMock(ITrieCache::class);
        $this->trieCompiler = $this->createMock(ITrieCompiler::class);
        $this->trieFactory = new TrieFactory($this->routeFactory, $this->trieCache, $this->trieCompiler);
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
        /** @var Route|MockObject $route */
        $route = $this->createMock(Route::class);
        $routeCollection = new RouteCollection();
        $routeCollection->add($route);
        $this->routeFactory->expects($this->once())
            ->method('createRoutes')
            ->willReturn($routeCollection);
        $this->trieCompiler->expects($this->once())
            ->method('compile')
            ->with($route)
            ->willReturn($expectedTrie);
        $this->trieCache->expects($this->once())
            ->method('set')
            ->with($expectedTrie);
        // Specifically not testing for same trie because createTrie() creates a brand new node on cache miss
        $this->assertEquals($expectedTrie, $this->trieFactory->createTrie());
    }

    public function testCreatingTrieWithNoCacheSetCreatesTrieFromCompiler(): void
    {
        $trieFactory = new TrieFactory($this->routeFactory, null, $this->trieCompiler);
        $expectedTrie = new RootTrieNode();
        /** @var Route|MockObject $route */
        $route = $this->createMock(Route::class);
        $routeCollection = new RouteCollection();
        $routeCollection->add($route);
        $this->routeFactory->expects($this->once())
            ->method('createRoutes')
            ->willReturn($routeCollection);
        $this->trieCompiler->expects($this->once())
            ->method('compile')
            ->with($route)
            ->willReturn($expectedTrie);
        // Specifically not testing for same trie because createTrie() creates a brand new node when not using a cache
        $this->assertEquals($expectedTrie, $trieFactory->createTrie());
    }
}
