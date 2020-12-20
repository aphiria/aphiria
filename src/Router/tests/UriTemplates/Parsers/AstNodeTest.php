<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Parsers;

use Aphiria\Routing\UriTemplates\Parsers\AstNode;
use Aphiria\Routing\UriTemplates\Parsers\AstNodeTypes;
use PHPUnit\Framework\TestCase;

class AstNodeTest extends TestCase
{
    public function testCheckingForChildrenReturnsCorrectValue(): void
    {
        $node = new AstNode('foo', 'bar');
        $this->assertFalse($node->hasChildren());
        $node->addChild(new AstNode('baz', 'blah'));
        $this->assertTrue($node->hasChildren());
    }

    public function testGettingChildReturnsCorrectNodes(): void
    {
        $node = new AstNode('foo', 'bar');
        $child = new AstNode('baz', 'blah');
        $node->addChild($child);
        $this->assertEquals([$child], $node->children);
    }

    public function testGettingTypeReturnsCorrectValue(): void
    {
        $expectedType = AstNodeTypes::VARIABLE;
        $this->assertSame($expectedType, (new AstNode($expectedType, 'foo'))->type);
    }

    public function testGettingValueReturnsCorrectValue(): void
    {
        $expectedValue = 'bar';
        $this->assertSame($expectedValue, (new AstNode('foo', $expectedValue))->value);
    }

    public function testNodeIsRootOnlyIfItHasNoParent(): void
    {
        $node = new AstNode('foo', 'bar');
        $child = new AstNode('baz', 'blah');
        $node->addChild($child);
        $this->assertTrue($node->isRoot());
        $this->assertFalse($child->isRoot());
    }

    public function testParentNodeIsSetOnChildNodes(): void
    {
        $node = new AstNode('foo', 'bar');
        $child = new AstNode('baz', 'blah');
        $node->addChild($child);
        $this->assertSame($node, $child->parent);
    }
}
