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

use Aphiria\Routing\Matchers\Trees\LiteralTrieNode;
use Aphiria\Routing\Matchers\Trees\TrieNode;
use Aphiria\Routing\MethodRouteAction;
use Aphiria\Routing\Route;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

/**
 * Tests the literal trie node
 */
class LiteralTrieNodeTest extends TestCase
{
    public function testCreatingWithSingleRouteConvertsItToArrayOfRoutes(): void
    {
        $expectedRoute = new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), []);
        $expectedHostTrie = $this->createMockNode();
        $node = new LiteralTrieNode('foo', [], $expectedRoute, $expectedHostTrie);
        $this->assertCount(1, $node->routes);
        $this->assertSame($expectedRoute, $node->routes[0]);
        $this->assertSame($expectedHostTrie, $node->hostTrie);
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedChildren = [new LiteralTrieNode('bar', [])];
        $expectedRoutes = [new Route(new UriTemplate(''), new MethodRouteAction('Foo', 'bar'), [])];
        $expectedHostTrie = $this->createMockNode();
        $node = new LiteralTrieNode('foo', $expectedChildren, $expectedRoutes, $expectedHostTrie);
        $this->assertEquals('foo', $node->value);
        $this->assertSame($expectedChildren, $node->getAllChildren());
        $this->assertSame($expectedRoutes, $node->routes);
        $this->assertSame($expectedHostTrie, $node->hostTrie);
    }

    /**
     * Creates a mock node for use in tests
     *
     * @return MockObject|TrieNode The mock node
     * @throws ReflectionException
     */
    private function createMockNode(): MockObject
    {
        return $this->getMockForAbstractClass(TrieNode::class, [], '', false);
    }
}
