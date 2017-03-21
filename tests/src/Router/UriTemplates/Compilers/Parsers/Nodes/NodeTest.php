<?php
namespace Opulence\Router\UriTemplates\Compilers\Parsers\Nodes;

/**
 * Tests the URI template parser node
 */
class NodeTest
{
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
}
