<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Input\Compilers\Tokenizers;

use Aphiria\Console\Input\Compilers\Tokenizers\ArgvInputTokenizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the argv input tokenizer
 */
class ArgvInputTokenizerTest extends TestCase
{
    /** @var ArgvInputTokenizer */
    private $tokenizer;

    protected function setUp(): void
    {
        $this->tokenizer = new ArgvInputTokenizer();
    }

    public function testTokenizingDoesNotRemoveFirstElementFromInputIfItDoesNotMatchApplicationName(): void
    {
        $tokenizerWithDefaultApplicationName = new ArgvInputTokenizer();
        $this->assertEquals(['foo', 'bar'], $tokenizerWithDefaultApplicationName->tokenize(['foo', 'bar']));
        $this->assertEquals(['foo', 'bar'], $tokenizerWithDefaultApplicationName->tokenize(['aphiria', 'foo', 'bar']));
        $tokenizerWithCustomApplicationName = new ArgvInputTokenizer('dave');
        $this->assertEquals(['foo', 'bar'], $tokenizerWithCustomApplicationName->tokenize(['foo', 'bar']));
        $this->assertEquals(['foo', 'bar'], $tokenizerWithCustomApplicationName->tokenize(['dave', 'foo', 'bar']));
    }

    public function testTokenizingNonArrayThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->tokenizer->tokenize('foo');
    }

    public function testTokenizingNullStringUsesArgvFromServerSuperglobal(): void
    {
        $_SERVER['argv'] = ['aphiria', 'foo'];
        $this->assertEquals(['foo'], $this->tokenizer->tokenize(null));
    }

    public function testTokenizingEscapedDoubleQuote(): void
    {
        $tokens = $this->tokenizer->tokenize(['aphiria', 'Dave\"s']);
        $this->assertEquals(['Dave"s'], $tokens);
    }

    public function testTokenizingEscapedSingleQuote(): void
    {
        $tokens = $this->tokenizer->tokenize(['aphiria', "Dave\'s"]);
        $this->assertEquals(["Dave's"], $tokens);
    }
}
