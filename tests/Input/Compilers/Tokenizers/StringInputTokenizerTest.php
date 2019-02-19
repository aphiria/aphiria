<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Input\Compilers\Tokenizers;

use Aphiria\Console\Input\Compilers\Tokenizers\StringInputTokenizer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the string input tokenizer
 */
class StringInputTokenizerTest extends TestCase
{
    /** @var StringInputTokenizer */
    private $tokenizer;

    public function setUp(): void
    {
        $this->tokenizer = new StringInputTokenizer();
    }

    public function testTokenizingArgumentAndOptionWithSpaceAroundIt(): void
    {
        $tokens = $this->tokenizer->tokenize("foo ' dave ' --last=' young '");
        $this->assertEquals([
            'foo',
            "' dave '",
            "--last=' young '"
        ], $tokens);
    }

    public function testTokenizingDoubleQuoteInsideSingleQuotes(): void
    {
        $tokens = $this->tokenizer->tokenize("foo '\"foo bar\"' --quote '\"Dave is cool\"'");
        $this->assertEquals([
            'foo',
            '\'"foo bar"\'',
            '--quote',
            '\'"Dave is cool"\'',
        ], $tokens);
    }

    public function testTokenizingOptionValueWithSpace(): void
    {
        $tokens = $this->tokenizer->tokenize("foo --name 'dave young'");
        $this->assertEquals([
            'foo',
            '--name',
            "'dave young'"
        ], $tokens);
    }

    public function testTokenizingSingleQuoteInsideDoubleQuotes(): void
    {
        $tokens = $this->tokenizer->tokenize("foo \"'foo bar'\" --quote \"'Dave is cool'\"");
        $this->assertEquals([
            'foo',
            "\"'foo bar'\"",
            '--quote',
            "\"'Dave is cool'\""
        ], $tokens);
    }

    public function testTokenizingUnclosedDoubleQuote(): void
    {
        $this->expectException(RuntimeException::class);
        $this->tokenizer->tokenize('foo "blah');
    }

    public function testTokenizingUnclosedSingleQuote(): void
    {
        $this->expectException(RuntimeException::class);
        $this->tokenizer->tokenize("foo 'blah");
    }

    public function testTokenizingWithExtraSpacesBetweenTokens(): void
    {
        $tokens = $this->tokenizer->tokenize(" foo   bar  --name='dave   young'  -r ");
        $this->assertEquals([
            'foo',
            'bar',
            "--name='dave   young'",
            '-r'
        ], $tokens);
    }
}
