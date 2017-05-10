<?php
namespace Opulence\Routing\Matchers\UriTemplates\Compilers\Parsers\Lexers\Tokens;

/**
 * Tests a lexer token
 */
class TokenTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests getting the type returns the correct value
     */
    public function testGettingTypeReturnsCorrectValue() : void
    {
        $expectedType = 'foo';
        $this->assertEquals($expectedType, (new Token('foo', 'bar'))->getType());
    }

    /**
     * Tests getting the value returns the correct value
     */
    public function testGettingValueReturnsCorrectValue() : void
    {
        $expectedValue = 'bar';
        $this->assertEquals($expectedValue, (new Token('foo', 'bar'))->getValue());
    }
}
