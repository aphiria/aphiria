<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Input\Tokenizers;

use Aphiria\Console\Input\Tokenizers\ArgvInputTokenizer;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

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
