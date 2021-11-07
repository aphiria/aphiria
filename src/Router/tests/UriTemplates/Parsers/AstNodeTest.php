<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Parsers;

use Aphiria\Routing\UriTemplates\Parsers\AstNode;
use Aphiria\Routing\UriTemplates\Parsers\AstNodeType;
use PHPUnit\Framework\TestCase;

class AstNodeTest extends TestCase
{
    public function testCheckingForChildrenReturnsCorrectValue(): void
    {
        $node = new AstNode(AstNodeType::Text, 'foo');
        $this->assertFalse($node->hasChildren());
        $node->addChild(new AstNode(AstNodeType::Text, 'bar'));
        $this->assertTrue($node->hasChildren());
    }

    public function testGettingChildReturnsCorrectNodes(): void
    {
        $node = new AstNode(AstNodeType::Text, 'foo');
        $child = new AstNode(AstNodeType::Text, 'bar');
        $node->addChild($child);
        $this->assertEquals([$child], $node->children);
    }

    public function testGettingTypeReturnsCorrectValue(): void
    {
        $expectedType = AstNodeType::Variable;
        $this->assertSame($expectedType, (new AstNode($expectedType, 'foo'))->type);
    }

    public function testGettingValueReturnsCorrectValue(): void
    {
        $expectedValue = 'bar';
        $this->assertSame($expectedValue, (new AstNode(AstNodeType::Text, $expectedValue))->value);
    }

    public function testNodeIsRootOnlyIfItHasNoParent(): void
    {
        $node = new AstNode(AstNodeType::Text, 'foo');
        $child = new AstNode(AstNodeType::Text, 'bar');
        $node->addChild($child);
        $this->assertTrue($node->isRoot());
        $this->assertFalse($child->isRoot());
    }

    public function testParentNodeIsSetOnChildNodes(): void
    {
        $node = new AstNode(AstNodeType::Text, 'foo');
        $child = new AstNode(AstNodeType::Text, 'bar');
        $node->addChild($child);
        $this->assertSame($node, $child->parent);
    }
}
