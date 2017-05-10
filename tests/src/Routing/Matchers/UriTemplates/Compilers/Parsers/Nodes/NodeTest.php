<?php
namespace Opulence\Routing\Matchers\UriTemplates\Compilers\Parsers\Nodes;

/**
 * Tests the URI template parser node
 */
class NodeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests checking for children returns correct value
     */
    public function testCheckingForChildrenReturnsCorrectValue() : void
    {
        $node = new Node('foo', 'bar');
        $this->assertFalse($node->hasChildren());
        $node->addChild(new Node('baz', 'blah'));
        $this->assertTrue($node->hasChildren());
    }

    /**
     * Tests getting the children returns the correct nodes
     */
    public function testGettingChildReturnsCorrectNodes() : void
    {
        $node = new Node('foo', 'bar');
        $child = new Node('baz', 'blah');
        $node->addChild($child);
        $this->assertEquals([$child], $node->getChildren());
    }

    /**
     * Tests getting the type returns the correct value
     */
    public function testGettingTypeReturnsCorrectValue() : void
    {
        $expectedType = NodeTypes::VARIABLE;
        $this->assertEquals($expectedType, (new Node($expectedType, 'foo'))->getType());
    }

    /**
     * Tests getting the value returns the correct value
     */
    public function testGettingValueReturnsCorrectValue() : void
    {
        $expectedValue = 'bar';
        $this->assertEquals($expectedValue, (new Node('foo', $expectedValue))->getValue());
    }

    /**
     * Tests that the a node is the root node only if it has no parent
     */
    public function testNodeIsRootOnlyIfItHasNoParent() : void
    {
        $node = new Node('foo', 'bar');
        $child = new Node('baz', 'blah');
        $node->addChild($child);
        $this->assertTrue($node->isRoot());
        $this->assertFalse($child->isRoot());
    }

    /**
     * Tests that the parent node is set on child nodes
     */
    public function testParentNodeIsSetOnChildNodes() : void
    {
        $node = new Node('foo', 'bar');
        $child = new Node('baz', 'blah');
        $node->addChild($child);
        $this->assertSame($node, $child->getParent());
    }
}
