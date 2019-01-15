<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers\Trees;

use InvalidArgumentException;
use Opulence\Routing\Matchers\Rules\IRule;
use Opulence\Routing\Matchers\Trees\LiteralTrieNode;
use Opulence\Routing\Matchers\Trees\RouteVariable;
use Opulence\Routing\Matchers\Trees\TrieNode;
use Opulence\Routing\Matchers\Trees\VariableTrieNode;
use Opulence\Routing\Route;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the variable trie node
 */
class VariableTrieNodeTest extends TestCase
{
    public function testCreatingWithEmptyPartsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must have at least one variable part');
        new VariableTrieNode([], []);
    }

    public function testCreatingWithSinglePartConvertsItToArrayOfParts(): void
    {
        $expectedPart = new RouteVariable('foo');
        $node = new VariableTrieNode($expectedPart, []);
        $this->assertCount(1, $node->parts);
        $this->assertSame($expectedPart, $node->parts[0]);
    }

    public function testCreatingWithSingleRouteConvertsItToArrayOfRoutes(): void
    {
        $expectedRoute = $this->createMock(Route::class);
        $node = new VariableTrieNode(new RouteVariable('foo'), [], $expectedRoute);
        $this->assertCount(1, $node->routes);
        $this->assertSame($expectedRoute, $node->routes[0]);
    }

    public function testIsMatchWithMultiplePartsReturnsTrueIfMatchesRegexAndSetsVariablesWithNoRules(): void
    {
        $node = new VariableTrieNode([new RouteVariable('foo'), 'baz'], []);
        $routeVariables = [];
        $this->assertTrue($node->isMatch('barbaz', $routeVariables));
        $this->assertEquals(['foo' => 'bar'], $routeVariables);
    }

    public function testIsMatchWithMultiplePartsReturnsTrueIfMatchesRegexWithDifferentCaseAndSetsVariables(): void
    {
        $node = new VariableTrieNode([new RouteVariable('foo'), 'baz'], []);
        $routeVariables = [];
        $this->assertTrue($node->isMatch('barBAZ', $routeVariables));
        $this->assertEquals(['foo' => 'bar'], $routeVariables);
    }

    public function testIsMatchWithMultiplePartsReturnsFalseIfAnyRuleFails(): void
    {
        $rule1 = $this->createMock(IRule::class);
        $rule1->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $rule2 = $this->createMock(IRule::class);
        $rule2->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(false);
        $node = new VariableTrieNode([new RouteVariable('foo', [$rule1, $rule2]), 'baz'], []);
        $routeVariables = [];
        $this->assertFalse($node->isMatch('barbaz', $routeVariables));
        $this->assertEquals([], $routeVariables);
    }

    public function testIsMatchWithMultiplePartsReturnsTrueIfMatchesRegexAndAllRulesPass(): void
    {
        $rule1 = $this->createMock(IRule::class);
        $rule1->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $rule2 = $this->createMock(IRule::class);
        $rule2->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $node = new VariableTrieNode([new RouteVariable('foo', [$rule1, $rule2]), 'baz'], []);
        $routeVariables = [];
        $this->assertTrue($node->isMatch('barbaz', $routeVariables));
        $this->assertEquals(['foo' => 'bar'], $routeVariables);
    }

    public function testIsMatchWithSingleRouteVariableReturnsTrueAndSetsVariablesWithNoRules(): void
    {
        $node = new VariableTrieNode(new RouteVariable('foo'), []);
        $routeVariables = [];
        $this->assertTrue($node->isMatch('bar', $routeVariables));
        $this->assertEquals(['foo' => 'bar'], $routeVariables);
    }

    public function testIsMatchWithSingleRouteVariableReturnsFalseIfAnyRuleFails(): void
    {
        $rule1 = $this->createMock(IRule::class);
        $rule1->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $rule2 = $this->createMock(IRule::class);
        $rule2->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(false);
        $node = new VariableTrieNode(new RouteVariable('foo', [$rule1, $rule2]), []);
        $routeVariables = [];
        $this->assertFalse($node->isMatch('bar', $routeVariables));
        $this->assertEquals([], $routeVariables);
    }

    public function testIsMatchWithSingleRouteVariableReturnsTrueIfAllRulesPass(): void
    {
        $rule1 = $this->createMock(IRule::class);
        $rule1->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $rule2 = $this->createMock(IRule::class);
        $rule2->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $node = new VariableTrieNode(new RouteVariable('foo', [$rule1, $rule2]), []);
        $routeVariables = [];
        $this->assertTrue($node->isMatch('bar', $routeVariables));
        $this->assertEquals(['foo' => 'bar'], $routeVariables);
    }

    public function testPartsCanContainStringsAndRouteVariables(): void
    {
        $expectedParts = [new RouteVariable('foo'), 'bar'];
        $node = new VariableTrieNode($expectedParts, []);
        $this->assertSame($expectedParts, $node->parts);
    }

    public function testPropertiesAreSetInConstructor(): void
    {
        $expectedParts = [new RouteVariable('foo')];
        $expectedChildren = [new LiteralTrieNode('bar', [])];
        $expectedRoutes = [$this->createMock(Route::class)];
        $expectedHostTrie = $this->createMockNode();
        $node = new VariableTrieNode($expectedParts, $expectedChildren, $expectedRoutes, $expectedHostTrie);
        $this->assertSame($expectedParts, $node->parts);
        $this->assertSame($expectedChildren, $node->getAllChildren());
        $this->assertSame($expectedRoutes, $node->routes);
        $this->assertSame($expectedHostTrie, $node->hostTrie);
    }

    /**
     * Creates a mock node for use in tests
     *
     * @return MockObject|TrieNode The mock node
     * @throws \ReflectionException
     */
    private function createMockNode(): MockObject
    {
        return $this->getMockForAbstractClass(TrieNode::class, [], '', false);
    }
}
