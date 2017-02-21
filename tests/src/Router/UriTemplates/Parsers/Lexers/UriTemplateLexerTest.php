<?php
namespace Opulence\Router\UriTemplates\Parsers\Lexers;

use Opulence\Router\UriTemplates\Parsers\Lexers\Tokens\Token;
use Opulence\Router\UriTemplates\Parsers\Lexers\Tokens\TokenTypes;

/**
 * Tests the URI template lexer
 */
class UriTemplateLexerTest extends \PHPUnit\Framework\TestCase
{
    /** @var UriTemplateLexer The lexer to use in tests */
    private $lexer = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->lexer = new UriTemplateLexer();
    }

    /**
     * Tests lexing a path with no variables
     */
    public function testLexingPathWithNoVariables(): void
    {
        $tokens = $this->lexer->lex('/foo/bar/baz');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/bar/baz')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path a variable at the end
     */
    public function testLexingPathWithVariableAtEnd(): void
    {
        $tokens = $this->lexer->lex('/foo/bar/:baz');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/bar/'),
                new Token(TokenTypes::T_VARIABLE_NAME, 'baz')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path a variable in the middle
     */
    public function testLexingPathWithVariableInMiddle(): void
    {
        $tokens = $this->lexer->lex('/foo/:bar/baz');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE_NAME, 'bar'),
                new Token(TokenTypes::T_TEXT, '/baz')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path a variable with a single rule
     */
    public function testLexingPathWithSingleRule(): void
    {
        $tokens = $this->lexer->lex('/foo/:bar(int)');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE_NAME, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_VARIABLE_RULE_SLUG, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path a variable with a multiple rules and multiple parameters with spaces in between
     */
    public function testLexingPathWithMultipleRulesAndParametersWithSpacesInBetween(): void
    {
        $tokens = $this->lexer->lex('/foo/:bar(baz( "1,2" , 3 ) , blah)');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE_NAME, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_VARIABLE_RULE_SLUG, 'baz'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_QUOTED_STRING, '1,2'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_NUMBER, '3'),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_VARIABLE_RULE_SLUG, 'blah'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path a variable with a single rule with a mix of string and number parameters
     */
    public function testLexingPathWithSingleRuleWithMixOfStringAndNumberParameters(): void
    {
        $tokens = $this->lexer->lex('/foo/:bar(baz("1,2",3))');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE_NAME, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_VARIABLE_RULE_SLUG, 'baz'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_QUOTED_STRING, '1,2'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_NUMBER, '3'),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path a variable with a single rule with multiple parameters
     */
    public function testLexingPathWithSingleRuleWithMultipleParameters(): void
    {
        $tokens = $this->lexer->lex('/foo/:bar(in(1,2,3))');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE_NAME, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_VARIABLE_RULE_SLUG, 'in'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_NUMBER, '1'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_NUMBER, '2'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_NUMBER, '3'),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path a variable with a single rule with a single parameter
     */
    public function testLexingPathWithSingleRuleWithSingleParameter(): void
    {
        $tokens = $this->lexer->lex('/foo/:bar(max(1))');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE_NAME, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_VARIABLE_RULE_SLUG, 'max'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_NUMBER, '1'),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path a variable with multiple rules
     */
    public function testLexingPathWithMultipleRule(): void
    {
        $tokens = $this->lexer->lex('/foo/:bar(int,caf)');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE_NAME, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_VARIABLE_RULE_SLUG, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_VARIABLE_RULE_SLUG, 'caf'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ],
            $tokens
        );
    }
}
