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
use Aphiria\Routing\UriTemplates\Compilers\Tries\RouteVariable;
use Aphiria\Routing\UriTemplates\Compilers\Tries\TrieNode;
use Aphiria\Routing\UriTemplates\Compilers\Tries\VariableTrieNode;
use Aphiria\Routing\UriTemplates\Constraints\IRouteVariableConstraint;
use Aphiria\Routing\UriTemplates\UriTemplate;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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
        $expectedRoute = new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), []);
        $node = new VariableTrieNode(new RouteVariable('foo'), [], $expectedRoute);
        $this->assertCount(1, $node->routes);
        $this->assertSame($expectedRoute, $node->routes[0]);
    }

    public function testInvalidRoutesThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Routes must be a route or an array of routes');
        new VariableTrieNode('foo', [], 1);
    }

    public function testIsMatchReturnsFalseIfRegexDoesNotMatch(): void
    {
        $node = new VariableTrieNode(['foo'], []);
        $routeVariables = [];
        $this->assertFalse($node->isMatch('bar', $routeVariables));
    }

    public function testIsMatchWithMultiplePartsReturnsTrueIfMatchesRegexAndSetsVariablesWithNoConstraints(): void
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

    public function testIsMatchWithMultiplePartsReturnsFalseIfAnyConstraintFails(): void
    {
        $constraint1 = $this->createMock(IRouteVariableConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $constraint2 = $this->createMock(IRouteVariableConstraint::class);
        $constraint2->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(false);
        $node = new VariableTrieNode([new RouteVariable('foo', [$constraint1, $constraint2]), 'baz'], []);
        $routeVariables = [];
        $this->assertFalse($node->isMatch('barbaz', $routeVariables));
        $this->assertEquals([], $routeVariables);
    }

    public function testIsMatchWithMultiplePartsReturnsTrueIfMatchesRegexAndAllConstraintsPass(): void
    {
        $constraint1 = $this->createMock(IRouteVariableConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $constraint2 = $this->createMock(IRouteVariableConstraint::class);
        $constraint2->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $node = new VariableTrieNode([new RouteVariable('foo', [$constraint1, $constraint2]), 'baz'], []);
        $routeVariables = [];
        $this->assertTrue($node->isMatch('barbaz', $routeVariables));
        $this->assertEquals(['foo' => 'bar'], $routeVariables);
    }

    public function testIsMatchWithSingleRouteVariableReturnsTrueAndSetsVariablesWithNoConstraints(): void
    {
        $node = new VariableTrieNode(new RouteVariable('foo'), []);
        $routeVariables = [];
        $this->assertTrue($node->isMatch('bar', $routeVariables));
        $this->assertEquals(['foo' => 'bar'], $routeVariables);
    }

    public function testIsMatchWithSingleRouteVariableReturnsFalseIfAnyConstraintFails(): void
    {
        $constraint1 = $this->createMock(IRouteVariableConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $constraint2 = $this->createMock(IRouteVariableConstraint::class);
        $constraint2->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(false);
        $node = new VariableTrieNode(new RouteVariable('foo', [$constraint1, $constraint2]), []);
        $routeVariables = [];
        $this->assertFalse($node->isMatch('bar', $routeVariables));
        $this->assertEquals([], $routeVariables);
    }

    public function testIsMatchWithSingleRouteVariableReturnsTrueIfAllConstraintsPass(): void
    {
        $constraint1 = $this->createMock(IRouteVariableConstraint::class);
        $constraint1->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $constraint2 = $this->createMock(IRouteVariableConstraint::class);
        $constraint2->expects($this->once())
            ->method('passes')
            ->with('bar')
            ->willReturn(true);
        $node = new VariableTrieNode(new RouteVariable('foo', [$constraint1, $constraint2]), []);
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
        $expectedRoutes = [new Route(new UriTemplate(''), new RouteAction('Foo', 'bar'), [])];
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
     */
    private function createMockNode(): MockObject
    {
        return $this->getMockForAbstractClass(TrieNode::class, [], '', false);
    }
}
