<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses\Compilers\Parsers\Nodes;

use Aphiria\Console\Tests\Responses\Compilers\Parsers\Nodes\Mocks\Node;
use PHPUnit\Framework\TestCase;

/**
 * Tests the response node
 */
class NodeTest extends TestCase
{
    /**
     * Tests adding a child
     */
    public function testAddingChild(): void
    {
        $parent = new Node('foo');
        $child = new Node('bar');
        $parent->addChild($child);
        $this->assertEquals([$child], $parent->getChildren());
        $this->assertSame($parent, $child->getParent());
    }

    /**
     * Tests checking if nodes are leaves
     */
    public function testCheckingIfLeaves(): void
    {
        $parent = new Node('foo');
        $child = new Node('bar');
        $this->assertSame($parent, $parent->addChild($child));
        $this->assertFalse($parent->isLeaf());
        $this->assertTrue($child->isLeaf());
    }

    /**
     * Tests checking if nodes are roots
     */
    public function testCheckingIfRoots(): void
    {
        $parent = new Node('foo');
        $child = new Node('bar');
        $parent->addChild($child);
        $this->assertTrue($parent->isRoot());
        $this->assertFalse($child->isRoot());
    }

    /**
     * Tests getting the value
     */
    public function testGettingValue(): void
    {
        $node = new Node('foo');
        $this->assertEquals('foo', $node->getValue());
    }
}
