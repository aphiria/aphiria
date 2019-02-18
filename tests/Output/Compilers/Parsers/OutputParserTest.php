<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Output\Compilers\Parsers;

use Aphiria\Console\Output\Compilers\Parsers\AbstractSyntaxTree;
use Aphiria\Console\Output\Compilers\Parsers\Lexers\Tokens\OutputToken;
use Aphiria\Console\Output\Compilers\Parsers\Lexers\Tokens\OutputTokenTypes;
use Aphiria\Console\Output\Compilers\Parsers\Nodes\TagNode;
use Aphiria\Console\Output\Compilers\Parsers\Nodes\WordNode;
use Aphiria\Console\Output\Compilers\Parsers\OutputParser;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the output parser
 */
class OutputParserTest extends TestCase
{
    /** @var OutputParser The parser to use in tests */
    private $parser;

    public function setUp(): void
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
        $expectedOutput = new AbstractSyntaxTree();
        $fooNode = new TagNode('foo');
        $fooNode->addChild(new WordNode('baz'));
        $expectedOutput->getCurrentNode()->addChild($fooNode);
        $barNode = new TagNode('bar');
        $barNode->addChild(new WordNode('blah'));
        $expectedOutput->getCurrentNode()->addChild($barNode);
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
        $expectedOutput = new AbstractSyntaxTree();
        $fooNode = new TagNode('foo');
        $expectedOutput->getCurrentNode()->addChild($fooNode);
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
        $expectedOutput = new AbstractSyntaxTree();
        $fooNode = new WordNode('<bar>');
        $expectedOutput->getCurrentNode()->addChild($fooNode);
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
        $expectedOutput = new AbstractSyntaxTree();
        $fooNode = new TagNode('foo');
        $fooNode->addChild(new WordNode('<bar>'));
        $expectedOutput->getCurrentNode()->addChild($fooNode);
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
        $expectedOutput = new AbstractSyntaxTree();
        $fooNode = new TagNode('foo');
        $fooNode->addChild(new WordNode('bar'));
        $barNode = new TagNode('bar');
        $barNode->addChild(new WordNode('blah'));
        $fooNode->addChild($barNode);
        $fooNode->addChild(new WordNode('baz'));
        $expectedOutput->getCurrentNode()->addChild($fooNode);
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
        $expectedOutput = new AbstractSyntaxTree();
        $expectedOutput->getCurrentNode()->addChild(new WordNode('dave'));
        $fooNode = new TagNode('foo');
        $fooNode->addChild(new WordNode('bar'));
        $barNode = new TagNode('bar');
        $barNode->addChild(new WordNode('blah'));
        $fooNode->addChild($barNode);
        $fooNode->addChild(new WordNode('baz'));
        $expectedOutput->getCurrentNode()->addChild($fooNode);
        $expectedOutput->getCurrentNode()->addChild(new WordNode('young'));
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
        $expectedOutput = new AbstractSyntaxTree();
        $fooNode = new TagNode('foo');
        $fooNode->addChild(new TagNode('bar'));
        $expectedOutput->getCurrentNode()->addChild($fooNode);
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
        $expectedOutput = new AbstractSyntaxTree();
        $node = new WordNode('foobar');
        $expectedOutput->getCurrentNode()->addChild($node);
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
        $expectedOutput = new AbstractSyntaxTree();
        $fooNode = new TagNode('foo');
        $fooNode->addChild(new WordNode('bar'));
        $expectedOutput->getCurrentNode()->addChild($fooNode);
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
