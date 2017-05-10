<?php
namespace Opulence\Routing\Matchers\UriTemplates\Compilers\Parsers\Lexers;

use InvalidArgumentException;
use Opulence\Routing\Matchers\UriTemplates\Compilers\Parsers\Lexers\Tokens\Token;
use Opulence\Routing\Matchers\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenStream;
use Opulence\Routing\Matchers\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenTypes;

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
     * Tests lexing a full URI
     */
    public function testLexingFullUri(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, 'https://example.com/foo/bar')
            ]),
            $this->lexer->lex('https://example.com/foo/bar')
        );
    }

    /**
     * Tests lexing a path with a default value
     */
    public function testLexingPathWithDefaultValue(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '='),
                new Token(TokenTypes::T_TEXT, 'baz')
            ]),
            $this->lexer->lex('/foo/:bar=baz')
        );
    }

    /**
     * Tests lexing a path with a default value and rules
     */
    public function testLexingPathWithDefaultValueAndRules(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '='),
                new Token(TokenTypes::T_TEXT, 'baz'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ]),
            $this->lexer->lex('/foo/:bar=baz(int)')
        );
    }

    /**
     * Tests lexing a path with a float
     */
    public function testLexingPathWithFloat() : void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_NUMBER, 1.23)
            ]),
            $this->lexer->lex('1.23')
        );
    }

    /**
     * Tests lexing a path with an int
     */
    public function testLexingPathWithInt() : void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_NUMBER, 123)
            ]),
            $this->lexer->lex('123')
        );
    }

    /**
     * Tests lexing a path a variable with multiple rules
     */
    public function testLexingPathWithMultipleRule(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_TEXT, 'caf'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ]),
            $this->lexer->lex('/foo/:bar(int,caf)')
        );
    }

    /**
     * Tests lexing a path a variable with multiple rules with spaces in between slugs and parameters
     */
    public function testLexingPathWithMultipleRulesWithSpacesInBetweenSlugsAndParameters(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_TEXT, 'caf'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'abc'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_TEXT, 'def'),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ]),
            $this->lexer->lex('/foo/:bar(int , caf(abc , def))')
        );
    }

    /**
     * Tests lexing a path with no variables
     */
    public function testLexingPathWithNoVariables(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/bar/baz')
            ]),
            $this->lexer->lex('/foo/bar/baz')
        );
    }

    /**
     * Tests lexing a path a variable with optional parts
     */
    public function testLexingPathWithOptionalParts(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo'),
                new Token(TokenTypes::T_PUNCTUATION, '['),
                new Token(TokenTypes::T_TEXT, '/bar'),
                new Token(TokenTypes::T_PUNCTUATION, ']')
            ]),
            $this->lexer->lex('/foo[/bar]')
        );
    }

    /**
     * Tests lexing a path a variable with a single rule
     */
    public function testLexingPathWithSingleRule(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ]),
            $this->lexer->lex('/foo/:bar(int)')
        );
    }

    /**
     * Tests lexing a path a variable with a single rule in the middle
     */
    public function testLexingPathWithSingleRuleInTheMiddle(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_TEXT, '/baz')
            ]),
            $this->lexer->lex('/foo/:bar(int)/baz')
        );
    }

    /**
     * Tests lexing a path a variable with a single rule with a mix of string and number parameters
     */
    public function testLexingPathWithSingleRuleWithMixOfStringAndNumberParameters(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'baz'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_QUOTED_STRING, '1,2'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_NUMBER, 3),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ]),
            $this->lexer->lex('/foo/:bar(baz("1,2",3))')
        );
    }

    /**
     * Tests lexing a path a variable with a single rule an array parameter
     */
    public function testLexingPathWithSingleRuleWithArrayParameter(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'baz'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_PUNCTUATION, '['),
                new Token(TokenTypes::T_NUMBER, 1),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_NUMBER, 2),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_QUOTED_STRING, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, ']'),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ]),
            $this->lexer->lex('/foo/:bar(baz([1,2,"foo"]))')
        );
    }

    /**
     * Tests lexing a path a variable with a single rule with multiple parameters
     */
    public function testLexingPathWithSingleRuleWithMultipleParameters(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'in'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_NUMBER, 1),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_NUMBER, 2),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_NUMBER, 3),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ]),
            $this->lexer->lex('/foo/:bar(in(1,2,3))')
        );
    }

    /**
     * Tests lexing a path a variable with a single rule with a single parameter
     */
    public function testLexingPathWithSingleRuleWithSingleParameter(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'max'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_NUMBER, 1),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ]),
            $this->lexer->lex('/foo/:bar(max(1))')
        );
    }

    /**
     * Tests lexing a path a variable at the beginning
     */
    public function testLexingPathWithVariableAtBeginning(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_VARIABLE, 'foo'),
                new Token(TokenTypes::T_TEXT, '/bar/baz')
            ]),
            $this->lexer->lex(':foo/bar/baz')
        );
    }

    /**
     * Tests lexing a path a variable at the end
     */
    public function testLexingPathWithVariableAtEnd(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/bar/'),
                new Token(TokenTypes::T_VARIABLE, 'baz')
            ]),
            $this->lexer->lex('/foo/bar/:baz')
        );
    }

    /**
     * Tests lexing a path a variable in the middle
     */
    public function testLexingPathWithVariableInMiddle(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_TEXT, '/baz')
            ]),
            $this->lexer->lex('/foo/:bar/baz')
        );
    }

    /**
     * Tests lexing a variable name that is soo long throws an exception
     */
    public function testLexingTooLongVariableNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $invalidVariableName = str_repeat('a', 33);
        $this->lexer->lex(":$invalidVariableName");
    }
}
