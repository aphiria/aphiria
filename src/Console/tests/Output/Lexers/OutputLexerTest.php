<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Lexers;

use Aphiria\Console\Output\Lexers\OutputLexer;
use Aphiria\Console\Output\Lexers\OutputToken;
use Aphiria\Console\Output\Lexers\OutputTokenType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class OutputLexerTest extends TestCase
{
    private OutputLexer $lexer;

    protected function setUp(): void
    {
        $this->lexer = new OutputLexer();
    }

    public function testLexingAdjacentElements(): void
    {
        $expectedOutput = [
            new OutputToken(OutputTokenType::TagOpen, 'foo', 0),
            new OutputToken(OutputTokenType::Word, 'baz', 5),
            new OutputToken(OutputTokenType::TagClose, 'foo', 8),
            new OutputToken(OutputTokenType::TagOpen, 'bar', 14),
            new OutputToken(OutputTokenType::Word, 'blah', 19),
            new OutputToken(OutputTokenType::TagClose, 'bar', 23),
            new OutputToken(OutputTokenType::Eof, null, 29)
        ];
        $this->assertEquals(
            $expectedOutput,
            $this->lexer->lex('<foo>baz</foo><bar>blah</bar>')
        );
    }

    public function testLexingElementWithNoChildren(): void
    {
        $expectedOutput = [
            new OutputToken(OutputTokenType::TagOpen, 'foo', 0),
            new OutputToken(OutputTokenType::TagClose, 'foo', 5),
            new OutputToken(OutputTokenType::Eof, null, 11)
        ];
        $this->assertEquals(
            $expectedOutput,
            $this->lexer->lex('<foo></foo>')
        );
    }

    public function testLexingEscapedTagAtBeginning(): void
    {
        $expectedOutput = [
            new OutputToken(OutputTokenType::Word, '<bar>', 1),
            new OutputToken(OutputTokenType::Eof, null, 6)
        ];
        $this->assertEquals($expectedOutput, $this->lexer->lex('\\<bar>'));
    }

    public function testLexingEscapedTagInBetweenTags(): void
    {
        $expectedOutput = [
            new OutputToken(OutputTokenType::TagOpen, 'foo', 0),
            new OutputToken(OutputTokenType::Word, '<bar>', 6),
            new OutputToken(OutputTokenType::TagClose, 'foo', 11),
            new OutputToken(OutputTokenType::Eof, null, 17)
        ];
        $this->assertEquals($expectedOutput, $this->lexer->lex('<foo>\\<bar></foo>'));
    }

    public function testLexingMultipleLines(): void
    {
        // We record the EOL length because it differs on OSs
        $eolLength = \strlen(PHP_EOL);
        $text = '<foo>' . PHP_EOL . 'bar' . PHP_EOL . '</foo>' . PHP_EOL . 'baz';
        $expectedOutput = [
            new OutputToken(OutputTokenType::TagOpen, 'foo', 0),
            new OutputToken(OutputTokenType::Word, PHP_EOL . 'bar' . PHP_EOL, 5),
            new OutputToken(OutputTokenType::TagClose, 'foo', 5 + 3 + (2 * $eolLength)),
            new OutputToken(OutputTokenType::Word, PHP_EOL . 'baz', 5 + 3 + (2 * $eolLength) + 6),
            new OutputToken(OutputTokenType::Eof, null, 5 + 3 + (3 * $eolLength) + 6 + 3)
        ];
        $this->assertEquals(
            $expectedOutput,
            $this->lexer->lex($text)
        );
    }

    public function testLexingNestedElements(): void
    {
        $expectedOutput = [
            new OutputToken(OutputTokenType::TagOpen, 'foo', 0),
            new OutputToken(OutputTokenType::Word, 'bar', 5),
            new OutputToken(OutputTokenType::TagOpen, 'bar', 8),
            new OutputToken(OutputTokenType::Word, 'blah', 13),
            new OutputToken(OutputTokenType::TagClose, 'bar', 17),
            new OutputToken(OutputTokenType::Word, 'baz', 23),
            new OutputToken(OutputTokenType::TagClose, 'foo', 26),
            new OutputToken(OutputTokenType::Eof, null, 32)
        ];
        $this->assertEquals(
            $expectedOutput,
            $this->lexer->lex('<foo>bar<bar>blah</bar>baz</foo>')
        );
    }

    public function testLexingNestedElementsWithNoChildren(): void
    {
        $expectedOutput = [
            new OutputToken(OutputTokenType::TagOpen, 'foo', 0),
            new OutputToken(OutputTokenType::TagOpen, 'bar', 5),
            new OutputToken(OutputTokenType::TagClose, 'bar', 10),
            new OutputToken(OutputTokenType::TagClose, 'foo', 16),
            new OutputToken(OutputTokenType::Eof, null, 22)
        ];
        $this->assertEquals(
            $expectedOutput,
            $this->lexer->lex('<foo><bar></bar></foo>')
        );
    }

    public function testLexingOpenTagInsideOfAnotherTagCreatesExceptionMessagesWithCorrectContexts(): void
    {
        // Test various permutations to make sure we're always getting the proper surround text context
        $inputs = [
            ['<<', 'Invalid tags near "<<", character #1'],
            ['<fo<', 'Invalid tags near "<fo<", character #3'],
            ['<foo<', 'Invalid tags near "foo<", character #4']
        ];

        foreach ($inputs as $input) {
            try {
                $this->lexer->lex($input[0]);
                $this->fail('Failed to throw exception');
            } catch (RuntimeException $ex) {
                $this->assertEquals($input[1], $ex->getMessage());
            }
        }
    }

    public function testLexingOpenTagAtEndOfInputIsIgnored(): void
    {
        $expectedOutput = [
            new OutputToken(OutputTokenType::Eof, null, 1)
        ];
        $this->assertEquals($expectedOutput, $this->lexer->lex('<'));
    }

    public function testLexingOpenTagInsideOfCloseTagThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid tags near "></<", character #7');
        $this->lexer->lex('<foo></<bar>foo>');
    }

    public function testLexingOpenTagInsideOfOpenTagThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid tags near "foo<", character #4');
        $this->lexer->lex('<foo<bar>>');
    }

    public function testLexingPlainText(): void
    {
        $expectedOutput = [
            new OutputToken(OutputTokenType::Word, 'foobar', 0),
            new OutputToken(OutputTokenType::Eof, null, 6)
        ];
        $this->assertEquals(
            $expectedOutput,
            $this->lexer->lex('foobar')
        );
    }

    public function testLexingSingleElement(): void
    {
        $expectedOutput = [
            new OutputToken(OutputTokenType::TagOpen, 'foo', 0),
            new OutputToken(OutputTokenType::Word, 'bar', 5),
            new OutputToken(OutputTokenType::TagClose, 'foo', 8),
            new OutputToken(OutputTokenType::Eof, null, 14)
        ];
        $this->assertEquals($expectedOutput, $this->lexer->lex('<foo>bar</foo>'));
    }

    public function testLexingUnopenedTag(): void
    {
        $expectedOutput = [
            new OutputToken(OutputTokenType::Word, 'foo', 0),
            new OutputToken(OutputTokenType::TagClose, 'bar', 3),
            new OutputToken(OutputTokenType::Eof, null, 9)
        ];
        $this->assertEquals($expectedOutput, $this->lexer->lex('foo</bar>'));
    }
}
