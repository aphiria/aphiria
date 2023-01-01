<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Compilers\Tries;

use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\Compilers\Tries\LiteralTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieNode;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LiteralTrieNodeTest extends TestCase
{
    public function testCreatingWithSingleRouteConvertsItToArrayOfRoutes(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $expectedRoute = new Route(new UriTemplate(''), new RouteAction($controller::class, 'bar'), []);
        $expectedHostTrie = $this->createMockNode();
        $node = new LiteralTrieNode('foo', [], $expectedRoute, $expectedHostTrie);
        $this->assertCount(1, $node->routes);
        $this->assertSame($expectedRoute, $node->routes[0]);
        $this->assertSame($expectedHostTrie, $node->hostTrie);
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $expectedChildren = [new LiteralTrieNode('bar', [])];
        $expectedRoutes = [new Route(new UriTemplate(''), new RouteAction($controller::class, 'bar'), [])];
        $expectedHostTrie = $this->createMockNode();
        $node = new LiteralTrieNode('foo', $expectedChildren, $expectedRoutes, $expectedHostTrie);
        $this->assertSame('foo', $node->value);
        $this->assertSame($expectedChildren, $node->getAllChildren());
        $this->assertSame($expectedRoutes, $node->routes);
        $this->assertSame($expectedHostTrie, $node->hostTrie);
    }

    /**
     * Creates a mock node for use in tests
     *
     * @return TrieNode&MockObject The mock node
     */
    private function createMockNode(): TrieNode&MockObject
    {
        return $this->getMockForAbstractClass(TrieNode::class, [], '', false);
    }
}
