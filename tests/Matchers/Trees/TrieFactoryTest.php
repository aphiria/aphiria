<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Matchers\Trees;

use Aphiria\Routing\IRouteFactory;
use Aphiria\Routing\Matchers\Trees\Caching\ITrieCache;
use Aphiria\Routing\Matchers\Trees\Compilers\ITrieCompiler;
use Aphiria\Routing\Matchers\Trees\RootTrieNode;
use Aphiria\Routing\Matchers\Trees\TrieFactory;
use Aphiria\Routing\MethodRouteAction;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the trie factory
 */
class TrieFactoryTest extends TestCase
{
    private TrieFactory $trieFactory;
    /** @var IRouteFactory|MockObject */
    private IRouteFactory $routeFactory;
    /** @var ITrieCache|MockObject */
    private ITrieCache $trieCache;
    /** @var ITrieCompiler|MockObject */
    private ITrieCompiler $trieCompiler;

    protected function setUp(): void
    {
        $this->routeFactory = $this->createMock(IRouteFactory::class);
        $this->trieCache = $this->createMock(ITrieCache::class);
        $this->trieCompiler = $this->createMock(ITrieCompiler::class);
        $this->trieFactory = new TrieFactory($this->routeFactory, $this->trieCache, $this->trieCompiler);
    }

    public function testCreatingTrieWithCacheHitReturnsTrieFromCache(): void
    {
        $this->routeFactory->expects($this->never())
            ->method('createRoutes');
        $expectedTrie = new RootTrieNode();
        $this->trieCache->expects($this->once())
            ->method('get')
            ->willReturn($expectedTrie);
        $this->assertSame($expectedTrie, $this->trieFactory->createTrie());
    }

    public function testCreatingTrieWithCacheMissSetsItInCache(): void
    {
        $this->routeFactory->expects($this->once())
            ->method('createRoutes')
            ->willReturn(new RouteCollection([
                new Route(new UriTemplate('foo'), new MethodRouteAction('Bar', 'baz'), [])
            ]));
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
        $this->routeFactory->expects($this->once())
            ->method('createRoutes')
            ->willReturn(new RouteCollection([
                new Route(new UriTemplate('foo'), new MethodRouteAction('Bar', 'baz'), [])
            ]));
        $trieFactory = new TrieFactory($this->routeFactory, null, $this->trieCompiler);
        $expectedTrie = new RootTrieNode();
        $this->trieCompiler->expects($this->once())
            ->method('compile')
            ->willReturn($expectedTrie);
        // Specifically not testing for same trie because createTrie() creates a brand new node when not using a cache
        $this->assertEquals($expectedTrie, $trieFactory->createTrie());
    }
}
