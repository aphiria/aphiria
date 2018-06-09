<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\UriTemplates\Compilers\Parsers\Nodes;

use Opulence\Routing\UriTemplates\Compilers\Parsers\Nodes\Node;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Nodes\NodeTypes;

/**
 * Tests the URI template parser node
 */
class NodeTest extends \PHPUnit\Framework\TestCase
{
    public function testCheckingForChildrenReturnsCorrectValue(): void
    {
        $node = new Node('foo', 'bar');
        $this->assertFalse($node->hasChildren());
        $node->addChild(new Node('baz', 'blah'));
        $this->assertTrue($node->hasChildren());
    }

    public function testGettingChildReturnsCorrectNodes(): void
    {
        $node = new Node('foo', 'bar');
        $child = new Node('baz', 'blah');
        $node->addChild($child);
        $this->assertEquals([$child], $node->getChildren());
    }

    public function testGettingTypeReturnsCorrectValue(): void
    {
        $expectedType = NodeTypes::VARIABLE;
        $this->assertEquals($expectedType, (new Node($expectedType, 'foo'))->getType());
    }

    public function testGettingValueReturnsCorrectValue(): void
    {
        $expectedValue = 'bar';
        $this->assertEquals($expectedValue, (new Node('foo', $expectedValue))->getValue());
    }

    public function testNodeIsRootOnlyIfItHasNoParent(): void
    {
        $node = new Node('foo', 'bar');
        $child = new Node('baz', 'blah');
        $node->addChild($child);
        $this->assertTrue($node->isRoot());
        $this->assertFalse($child->isRoot());
    }

    public function testParentNodeIsSetOnChildNodes(): void
    {
        $node = new Node('foo', 'bar');
        $child = new Node('baz', 'blah');
        $node->addChild($child);
        $this->assertSame($node, $child->getParent());
    }
}
