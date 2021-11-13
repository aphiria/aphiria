<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\UriTemplates\Lexers;

use Aphiria\Routing\UriTemplates\Lexers\LexingException;
use Aphiria\Routing\UriTemplates\Lexers\Token;
use Aphiria\Routing\UriTemplates\Lexers\TokenStream;
use Aphiria\Routing\UriTemplates\Lexers\TokenType;
use Aphiria\Routing\UriTemplates\Lexers\UriTemplateLexer;
use PHPUnit\Framework\TestCase;

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
     * @param float $number The number to lex
     * @param string $uriTemplate The
     */
    public function testLexingPathWithFloat(float $number, string $uriTemplate): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Number, $number)
            ]),
            $this->lexer->lex($uriTemplate)
        );
    }

    public function testLexingPathWithInt(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Number, 123)
            ]),
            $this->lexer->lex('123')
        );
    }

    public function testLexingHostWithNoVariablesCreatesTextAndPunctuationTokens(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Text, 'example'),
                new Token(TokenType::Punctuation, '.'),
                new Token(TokenType::Text, 'com')
            ]),
            $this->lexer->lex('example.com')
        );
    }

    public function testLexingHostWithOptionalPartCreatesTextAndPunctuationTokens(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '['),
                new Token(TokenType::Text, 'api'),
                new Token(TokenType::Punctuation, '.'),
                new Token(TokenType::Punctuation, ']'),
                new Token(TokenType::Text, 'example'),
                new Token(TokenType::Punctuation, '.'),
                new Token(TokenType::Text, 'com')
            ]),
            $this->lexer->lex('[api.]example.com')
        );
    }

    public function testLexingHostWithVariableCreatesTextAndPunctuationTokens(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Variable, 'foo'),
                new Token(TokenType::Punctuation, '.'),
                new Token(TokenType::Text, 'example'),
                new Token(TokenType::Punctuation, '.'),
                new Token(TokenType::Text, 'com')
            ]),
            $this->lexer->lex(':foo.example.com')
        );
    }

    public function testLexingPathWithMultipleConstraint(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Variable, 'bar'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Text, 'int'),
                new Token(TokenType::Punctuation, ','),
                new Token(TokenType::Text, 'caf'),
                new Token(TokenType::Punctuation, ')')
            ]),
            $this->lexer->lex('/foo/:bar(int,caf)')
        );
    }

    public function testLexingPathWithMultipleConstraintsWithSpacesInBetweenSlugsAndParameters(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Variable, 'bar'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Text, 'int'),
                new Token(TokenType::Punctuation, ','),
                new Token(TokenType::Text, 'caf'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Text, 'abc'),
                new Token(TokenType::Punctuation, ','),
                new Token(TokenType::Text, 'def'),
                new Token(TokenType::Punctuation, ')'),
                new Token(TokenType::Punctuation, ')')
            ]),
            $this->lexer->lex('/foo/:bar(int , caf(abc , def))')
        );
    }

    public function testLexingPathWithNoVariables(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'bar'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'baz'),
            ]),
            $this->lexer->lex('/foo/bar/baz')
        );
    }

    public function testLexingPathWithOptionalParts(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '['),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'bar'),
                new Token(TokenType::Punctuation, ']')
            ]),
            $this->lexer->lex('/foo[/bar]')
        );
    }

    public function testLexingPathWithSingleConstraint(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Variable, 'bar'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Text, 'int'),
                new Token(TokenType::Punctuation, ')')
            ]),
            $this->lexer->lex('/foo/:bar(int)')
        );
    }

    public function testLexingPathWithSingleConstraintInTheMiddle(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Variable, 'bar'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Text, 'int'),
                new Token(TokenType::Punctuation, ')'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'baz')
            ]),
            $this->lexer->lex('/foo/:bar(int)/baz')
        );
    }

    public function testLexingPathWithSingleConstraintWithMixOfStringAndNumberParameters(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Variable, 'bar'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Text, 'baz'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::QuotedString, '1,2'),
                new Token(TokenType::Punctuation, ','),
                new Token(TokenType::Number, 3),
                new Token(TokenType::Punctuation, ')'),
                new Token(TokenType::Punctuation, ')')
            ]),
            $this->lexer->lex('/foo/:bar(baz("1,2",3))')
        );
    }

    public function testLexingPathWithSingleConstraintWithArrayParameter(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Variable, 'bar'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Text, 'baz'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Punctuation, '['),
                new Token(TokenType::Number, 1),
                new Token(TokenType::Punctuation, ','),
                new Token(TokenType::Number, 2),
                new Token(TokenType::Punctuation, ','),
                new Token(TokenType::QuotedString, 'foo'),
                new Token(TokenType::Punctuation, ']'),
                new Token(TokenType::Punctuation, ')'),
                new Token(TokenType::Punctuation, ')')
            ]),
            $this->lexer->lex('/foo/:bar(baz([1,2,"foo"]))')
        );
    }

    public function testLexingPathWithSingleConstraintWithMultipleParameters(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Variable, 'bar'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Text, 'in'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Number, 1),
                new Token(TokenType::Punctuation, ','),
                new Token(TokenType::Number, 2),
                new Token(TokenType::Punctuation, ','),
                new Token(TokenType::Number, 3),
                new Token(TokenType::Punctuation, ')'),
                new Token(TokenType::Punctuation, ')')
            ]),
            $this->lexer->lex('/foo/:bar(in(1,2,3))')
        );
    }

    public function testLexingPathWithSingleConstraintWithSingleParameter(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Variable, 'bar'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Text, 'max'),
                new Token(TokenType::Punctuation, '('),
                new Token(TokenType::Number, 1),
                new Token(TokenType::Punctuation, ')'),
                new Token(TokenType::Punctuation, ')')
            ]),
            $this->lexer->lex('/foo/:bar(max(1))')
        );
    }

    public function testLexingPathWithVariableAtBeginning(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Variable, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'bar'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'baz')
            ]),
            $this->lexer->lex(':foo/bar/baz')
        );
    }

    public function testLexingPathWithVariableAtEnd(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'bar'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Variable, 'baz')
            ]),
            $this->lexer->lex('/foo/bar/:baz')
        );
    }

    public function testLexingPathWithVariableInMiddle(): void
    {
        $this->assertEquals(
            new TokenStream([
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'foo'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Variable, 'bar'),
                new Token(TokenType::Punctuation, '/'),
                new Token(TokenType::Text, 'baz')
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
