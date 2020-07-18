<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Compilers\Tries;

use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\Compilers\Tries\LiteralTrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieNode;
use Aphiria\Routing\UriTemplates\UriTemplate;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class LiteralTrieNodeTest extends TestCase
{
    public function testCreatingWithSingleRouteConvertsItToArrayOfRoutes(): void
    {
        $expectedRoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
        $expectedHostTrie = $this->createMockNode();
        $node = new LiteralTrieNode('foo', [], $expectedRoute, $expectedHostTrie);
        $this->assertCount(1, $node->routes);
        $this->assertSame($expectedRoute, $node->routes[0]);
        $this->assertSame($expectedHostTrie, $node->hostTrie);
    }

    public function testInvalidRoutesThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Routes must be a route or an array of routes');
        new LiteralTrieNode('foo', [], 1);
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedChildren = [new LiteralTrieNode('bar', [])];
        $expectedRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
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
