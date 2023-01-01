<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Parsers;

use Aphiria\Console\Tests\Output\Parsers\Mocks\AstNode;
use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    public function testAddingChild(): void
    {
        $parent = new AstNode('foo');
        $child = new AstNode('bar');
        $parent->addChild($child);
        $this->assertEquals([$child], $parent->children);
        $this->assertSame($parent, $child->parent);
    }

    public function testCheckingIfLeaves(): void
    {
        $parent = new AstNode('foo');
        $child = new AstNode('bar');
        $this->assertSame($parent, $parent->addChild($child));
        $this->assertFalse($parent->isLeaf());
        $this->assertTrue($child->isLeaf());
    }

    public function testCheckingIfRoots(): void
    {
        $parent = new AstNode('foo');
        $child = new AstNode('bar');
        $parent->addChild($child);
        $this->assertTrue($parent->isRoot());
        $this->assertFalse($child->isRoot());
    }

    public function testGettingValue(): void
    {
        $node = new AstNode('foo');
        $this->assertSame('foo', $node->value);
    }
}
