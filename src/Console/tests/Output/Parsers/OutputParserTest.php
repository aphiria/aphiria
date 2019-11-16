<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output\Parsers;

use Aphiria\Console\Output\Lexers\OutputToken;
use Aphiria\Console\Output\Lexers\OutputTokenTypes;
use Aphiria\Console\Output\Parsers\OutputParser;
use Aphiria\Console\Output\Parsers\RootAstNode;
use Aphiria\Console\Output\Parsers\TagAstNode;
use Aphiria\Console\Output\Parsers\WordAstNode;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the output parser
 */
class OutputParserTest extends TestCase
{
    private OutputParser $parser;

    protected function setUp(): void
    {
        $this->parser = new OutputParser();
    }

    public function testIncorrectlyNestedTags(): void
    {
        $this->expectException(RuntimeException::class);
        $tokens = [
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'blah', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_EOF, null, 1)
        ];
        $this->parser->parse($tokens);
    }

    public function testParsingAdjacentElements(): void
    {
        $tokens = [
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'baz', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'blah', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_EOF, null, 1)
        ];
        $expectedOutput = new RootAstNode();
        $fooNode = new TagAstNode('foo');
        $fooNode->addChild(new WordAstNode('baz'));
        $expectedOutput->addChild($fooNode);
        $barNode = new TagAstNode('bar');
        $barNode->addChild(new WordAstNode('blah'));
        $expectedOutput->addChild($barNode);
        $this->assertEquals(
            $expectedOutput,
            $this->parser->parse($tokens)
        );
    }

    public function testParsingElementWithNoChildren(): void
    {
        $tokens = [
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_EOF, null, 1)
        ];
        $expectedOutput = new RootAstNode();
        $fooNode = new TagAstNode('foo');
        $expectedOutput->addChild($fooNode);
        $this->assertEquals(
            $expectedOutput,
            $this->parser->parse($tokens)
        );
    }

    public function testParsingEscapedTagAtBeginning(): void
    {
        $tokens = [
            new OutputToken(OutputTokenTypes::T_WORD, '<bar>', 1),
            new OutputToken(OutputTokenTypes::T_EOF, null, 1)
        ];
        $expectedOutput = new RootAstNode();
        $fooNode = new WordAstNode('<bar>');
        $expectedOutput->addChild($fooNode);
        $this->assertEquals($expectedOutput, $this->parser->parse($tokens));
    }

    public function testParsingEscapedTagInBetweenTags(): void
    {
        $tokens = [
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_WORD, '<bar>', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_EOF, null, 1)
        ];
        $expectedOutput = new RootAstNode();
        $fooNode = new TagAstNode('foo');
        $fooNode->addChild(new WordAstNode('<bar>'));
        $expectedOutput->addChild($fooNode);
        $this->assertEquals($expectedOutput, $this->parser->parse($tokens));
    }

    public function testParsingNestedElements(): void
    {
        $tokens = [
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'blah', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'baz', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_EOF, null, 1)
        ];
        $expectedOutput = new RootAstNode();
        $fooNode = new TagAstNode('foo');
        $fooNode->addChild(new WordAstNode('bar'));
        $barNode = new TagAstNode('bar');
        $barNode->addChild(new WordAstNode('blah'));
        $fooNode->addChild($barNode);
        $fooNode->addChild(new WordAstNode('baz'));
        $expectedOutput->addChild($fooNode);
        $this->assertEquals(
            $expectedOutput,
            $this->parser->parse($tokens)
        );
    }

    public function testParsingNestedElementsSurroundedByWords(): void
    {
        $tokens = [
            new OutputToken(OutputTokenTypes::T_WORD, 'dave', 1),
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'blah', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'baz', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'young', 1),
            new OutputToken(OutputTokenTypes::T_EOF, null, 1)
        ];
        $expectedOutput = new RootAstNode();
        $expectedOutput->addChild(new WordAstNode('dave'));
        $fooNode = new TagAstNode('foo');
        $fooNode->addChild(new WordAstNode('bar'));
        $barNode = new TagAstNode('bar');
        $barNode->addChild(new WordAstNode('blah'));
        $fooNode->addChild($barNode);
        $fooNode->addChild(new WordAstNode('baz'));
        $expectedOutput->addChild($fooNode);
        $expectedOutput->addChild(new WordAstNode('young'));
        $this->assertEquals(
            $expectedOutput,
            $this->parser->parse($tokens)
        );
    }

    public function testParsingNestedElementsWithNoChildren(): void
    {
        $tokens = [
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_EOF, null, 1)
        ];
        $expectedOutput = new RootAstNode();
        $fooNode = new TagAstNode('foo');
        $fooNode->addChild(new TagAstNode('bar'));
        $expectedOutput->addChild($fooNode);
        $this->assertEquals(
            $expectedOutput,
            $this->parser->parse($tokens)
        );
    }

    public function testParsingPlainText(): void
    {
        $tokens = [
            new OutputToken(OutputTokenTypes::T_WORD, 'foobar', 1),
            new OutputToken(OutputTokenTypes::T_EOF, null, 1)
        ];
        $expectedOutput = new RootAstNode();
        $node = new WordAstNode('foobar');
        $expectedOutput->addChild($node);
        $this->assertEquals(
            $expectedOutput,
            $this->parser->parse($tokens)
        );
    }

    public function testParsingSingleElement(): void
    {
        $tokens = [
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_EOF, null, 1)
        ];
        $expectedOutput = new RootAstNode();
        $fooNode = new TagAstNode('foo');
        $fooNode->addChild(new WordAstNode('bar'));
        $expectedOutput->addChild($fooNode);
        $this->assertEquals($expectedOutput, $this->parser->parse($tokens));
    }

    public function testParsingWithUnclosedTag(): void
    {
        $this->expectException(RuntimeException::class);
        $tokens = [
            new OutputToken(OutputTokenTypes::T_TAG_OPEN, 'foo', 1),
            new OutputToken(OutputTokenTypes::T_WORD, 'bar', 1),
            new OutputToken(OutputTokenTypes::T_EOF, null, 1)
        ];
        $this->parser->parse($tokens);
    }

    public function testParsingWithUnopenedTag(): void
    {
        $this->expectException(RuntimeException::class);
        $tokens = [
            new OutputToken(OutputTokenTypes::T_WORD, 'foo', 0),
            new OutputToken(OutputTokenTypes::T_TAG_CLOSE, 'bar', 3),
            new OutputToken(OutputTokenTypes::T_EOF, null, 9)
        ];
        $this->parser->parse($tokens);
    }
}
