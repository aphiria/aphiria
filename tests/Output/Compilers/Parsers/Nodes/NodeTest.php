<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Output\Compilers\Parsers\Nodes;

use Aphiria\Console\Tests\Output\Compilers\Parsers\Nodes\Mocks\Node;
use PHPUnit\Framework\TestCase;

/**
 * Tests the output node
 */
class NodeTest extends TestCase
{
    public function testAddingChild(): void
    {
        $parent = new Node('foo');
        $child = new Node('bar');
        $parent->addChild($child);
        $this->assertEquals([$child], $parent->children);
        $this->assertSame($parent, $child->parent);
    }

    public function testCheckingIfLeaves(): void
    {
        $parent = new Node('foo');
        $child = new Node('bar');
        $this->assertSame($parent, $parent->addChild($child));
        $this->assertFalse($parent->isLeaf());
        $this->assertTrue($child->isLeaf());
    }

    public function testCheckingIfRoots(): void
    {
        $parent = new Node('foo');
        $child = new Node('bar');
        $parent->addChild($child);
        $this->assertTrue($parent->isRoot());
        $this->assertFalse($child->isRoot());
    }

    public function testGettingValue(): void
    {
        $node = new Node('foo');
        $this->assertEquals('foo', $node->value);
    }
}
