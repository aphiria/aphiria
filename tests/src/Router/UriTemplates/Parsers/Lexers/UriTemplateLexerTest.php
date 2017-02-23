<?php
namespace Opulence\Router\UriTemplates\Parsers\Lexers;

use InvalidArgumentException;
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
     * Tests lexing a full URI
     */
    public function testLexingFullUri(): void
    {
        $tokens = $this->lexer->lex('https://example.com/foo/bar');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, 'https://example.com/foo/bar')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path with a default value
     */
    public function testLexingPathWithDefaultValue(): void
    {
        $tokens = $this->lexer->lex('/foo/:bar=baz');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '='),
                new Token(TokenTypes::T_TEXT, 'baz')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path with a default value and rules
     */
    public function testLexingPathWithDefaultValueAndRules(): void
    {
        $tokens = $this->lexer->lex('/foo/:bar=baz(int)');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '='),
                new Token(TokenTypes::T_TEXT, 'baz'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ],
            $tokens
        );
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
     * Tests lexing a path a variable at the beginning
     */
    public function testLexingPathWithVariableAtBeginning(): void
    {
        $tokens = $this->lexer->lex(':foo/bar/baz');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_VARIABLE, 'foo'),
                new Token(TokenTypes::T_TEXT, '/bar/baz')
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
                new Token(TokenTypes::T_VARIABLE, 'baz')
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
                new Token(TokenTypes::T_VARIABLE, 'bar'),
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
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path a variable with a single rule in the middle
     */
    public function testLexingPathWithSingleRuleInTheMiddle(): void
    {
        $tokens = $this->lexer->lex('/foo/:bar(int)/baz');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_TEXT, '/baz')
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
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'baz'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_QUOTED_STRING, '1,2'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_TEXT, '3'),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path a variable with a single rule an array parameter
     */
    public function testLexingPathWithSingleRuleWithArrayParameter(): void
    {
        $tokens = $this->lexer->lex('/foo/:bar(baz([1,2,"foo"]))');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'baz'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_PUNCTUATION, '['),
                new Token(TokenTypes::T_TEXT, '1'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_TEXT, '2'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_QUOTED_STRING, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, ']'),
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
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'in'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, '1'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_TEXT, '2'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_TEXT, '3'),
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
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'max'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, '1'),
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
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ','),
                new Token(TokenTypes::T_TEXT, 'caf'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ],
            $tokens
        );
    }

    /**
     * Tests lexing a path a variable with optional parts
     */
    public function testLexingPathWithOptionalParts(): void
    {
        $tokens = $this->lexer->lex('/foo[/bar]');
        $this->assertEquals(
            [
                new Token(TokenTypes::T_TEXT, '/foo'),
                new Token(TokenTypes::T_PUNCTUATION, '['),
                new Token(TokenTypes::T_TEXT, '/bar'),
                new Token(TokenTypes::T_PUNCTUATION, ']')
            ],
            $tokens
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
