<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Compilers\Tries;

use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\Compilers\Tries\Caching\ITrieCache;
use Aphiria\Routing\UriTemplates\Compilers\Tries\ITrieCompiler;
use Aphiria\Routing\UriTemplates\Compilers\Tries\LiteralTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\RootTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieFactory;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TrieFactoryTest extends TestCase
{
    private TrieFactory $trieFactory;
    private RouteCollection $routes;
    private ITrieCache&MockObject $trieCache;
    private ITrieCompiler&MockObject $trieCompiler;

    protected function setUp(): void
    {
        $this->routes = new RouteCollection();
        $this->trieCache = $this->createMock(ITrieCache::class);
        $this->trieCompiler = $this->createMock(ITrieCompiler::class);
        $this->trieFactory = new TrieFactory($this->routes, $this->trieCache, $this->trieCompiler);
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
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routes->add(new Route(new UriTemplate('foo'), new RouteAction($controller::class, 'bar'), []));
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
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routes->add(new Route(new UriTemplate('foo'), new RouteAction($controller::class, 'bar'), []));
        $trieFactory = new TrieFactory($this->routes, null, $this->trieCompiler);
        $expectedTrie = new RootTrieNode();
        // Make sure child nodes get added, too
        $expectedTrie->addChild(new LiteralTrieNode('foo', []));
        $this->trieCompiler->expects($this->once())
            ->method('compile')
            ->willReturn($expectedTrie);
        // Specifically not testing for same trie because createTrie() creates a brand new node when not using a cache
        $this->assertEquals($expectedTrie, $trieFactory->createTrie());
    }
}
