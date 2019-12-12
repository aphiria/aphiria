<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Lexers;

use Aphiria\Routing\UriTemplates\Lexers\LexingException;
use Aphiria\Routing\UriTemplates\Lexers\Token;
use Aphiria\Routing\UriTemplates\Lexers\TokenStream;
use Aphiria\Routing\UriTemplates\Lexers\TokenTypes;
use Aphiria\Routing\UriTemplates\Lexers\UriTemplateLexer;
use PHPUnit\Framework\TestCase;

/**
 * Tests the URI template lexer
 */
class UriTemplateLexerTest extends TestCase
{
    private UriTemplateLexer $lexer;

    protected function setUp(): void
    {
        $this->lexer = new UriTemplateLexer();
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

    public function testLexingHostWithNoVariablesCreatesTextAndPunctuationTokens(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_TEXT, 'example'),
                new Token(TokenTypes::T_PUNCTUATION, '.'),
                new Token(TokenTypes::T_TEXT, 'com')
            ]),
            $this->lexer->lex('example.com')
        );
    }

    public function testLexingHostWithOptionalPartCreatesTextAndPunctuationTokens(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '['),
                new Token(TokenTypes::T_TEXT, 'api'),
                new Token(TokenTypes::T_PUNCTUATION, '.'),
                new Token(TokenTypes::T_PUNCTUATION, ']'),
                new Token(TokenTypes::T_TEXT, 'example'),
                new Token(TokenTypes::T_PUNCTUATION, '.'),
                new Token(TokenTypes::T_TEXT, 'com')
            ]),
            $this->lexer->lex('[api.]example.com')
        );
    }

    public function testLexingHostWithVariableCreatesTextAndPunctuationTokens(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_VARIABLE, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '.'),
                new Token(TokenTypes::T_TEXT, 'example'),
                new Token(TokenTypes::T_PUNCTUATION, '.'),
                new Token(TokenTypes::T_TEXT, 'com')
            ]),
            $this->lexer->lex(':foo.example.com')
        );
    }

    public function testLexingPathWithMultipleConstraint(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
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

    public function testLexingPathWithMultipleConstraintsWithSpacesInBetweenSlugsAndParameters(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
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
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'baz'),
            ]),
            $this->lexer->lex('/foo/bar/baz')
        );
    }

    public function testLexingPathWithOptionalParts(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '['),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, ']')
            ]),
            $this->lexer->lex('/foo[/bar]')
        );
    }

    public function testLexingPathWithSingleConstraint(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ')')
            ]),
            $this->lexer->lex('/foo/:bar(int)')
        );
    }

    public function testLexingPathWithSingleConstraintInTheMiddle(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '('),
                new Token(TokenTypes::T_TEXT, 'int'),
                new Token(TokenTypes::T_PUNCTUATION, ')'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'baz')
            ]),
            $this->lexer->lex('/foo/:bar(int)/baz')
        );
    }

    public function testLexingPathWithSingleConstraintWithMixOfStringAndNumberParameters(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
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

    public function testLexingPathWithSingleConstraintWithArrayParameter(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
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

    public function testLexingPathWithSingleConstraintWithMultipleParameters(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
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

    public function testLexingPathWithSingleConstraintWithSingleParameter(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
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
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'baz')
            ]),
            $this->lexer->lex(':foo/bar/baz')
        );
    }

    public function testLexingPathWithVariableAtEnd(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_VARIABLE, 'baz')
            ]),
            $this->lexer->lex('/foo/bar/:baz')
        );
    }

    public function testLexingPathWithVariableInMiddle(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'foo'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_VARIABLE, 'bar'),
                new Token(TokenTypes::T_PUNCTUATION, '/'),
                new Token(TokenTypes::T_TEXT, 'baz')
            ]),
            $this->lexer->lex('/foo/:bar/baz')
        );
    }

    public function testLexingTooLongVariableNameThrowsException(): void
    {
        $this->expectException(LexingException::class);
        $this->expectExceptionMessage('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
        $invalidVariableName = \str_repeat('a', 33);
        $this->lexer->lex(":$invalidVariableName");
    }
}
