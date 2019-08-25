<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Input\Compilers\Tokenizers;

use Aphiria\Console\Input\Compilers\Tokenizers\ArgvInputTokenizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the argv input tokenizer
 */
class ArgvInputTokenizerTest extends TestCase
{
    private ArgvInputTokenizer $tokenizer;

    protected function setUp(): void
    {
        $this->tokenizer = new ArgvInputTokenizer();
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
