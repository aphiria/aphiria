<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\UriTemplates\Compilers\Parsers\Lexers;

use InvalidArgumentException;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers\Tokens\Token;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenStream;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenTypes;
use Opulence\Routing\UriTemplates\Compilers\Parsers\Lexers\UriTemplateLexer;

/**
 * Tests the URI template lexer
 */
class UriTemplateLexerTest extends \PHPUnit\Framework\TestCase
{
    /** @var UriTemplateLexer The lexer to use in tests */
    private $lexer;

    public function setUp(): void
    {
        $this->lexer = new UriTemplateLexer();
    }

    public function testLexingFullUri(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, 'https://example.com/foo/bar')
            ]),
            $this->lexer->lex('https://example.com/foo/bar')
        );
    }

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

    public function lexingPathWithFloatProvider(): array
    {
        return [
            [1.23, '1.23'],
            [123.0, '123.0'],
        ];
    }

    /**
     * @dataProvider lexingPathWithFloatProvider
     */
    public function testLexingPathWithFloat($number, $expectedValue): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_NUMBER, $number)
            ]),
            $this->lexer->lex($expectedValue)
        );
    }

    public function testLexingPathWithInt(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_NUMBER, 123)
            ]),
            $this->lexer->lex('123')
        );
    }

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

    public function testLexingPathWithNoVariables(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, '/foo/bar/baz')
            ]),
            $this->lexer->lex('/foo/bar/baz')
        );
    }

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

    public function testLexingTooLongVariableNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $invalidVariableName = str_repeat('a', 33);
        $this->lexer->lex(":$invalidVariableName");
    }
}
