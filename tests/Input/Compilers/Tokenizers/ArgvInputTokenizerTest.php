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
use PHPUnit\Framework\TestCase;

/**
 * Tests the argv input tokenizer
 */
class ArgvInputTokenizerTest extends TestCase
{
    /** @var ArgvInputTokenizer The tokenizer to use in tests */
    private $tokenizer;

    public function setUp(): void
    {
        $this->tokenizer = new ArgvInputTokenizer();
    }

    public function testTokenizingEscapedDoubleQuote(): void
    {
        $tokens = $this->tokenizer->tokenize(['foo', 'Dave\"s']);
        $this->assertEquals(['Dave"s'], $tokens);
    }

    public function testTokenizingEscapedSingleQuote(): void
    {
        $tokens = $this->tokenizer->tokenize(['foo', "Dave\'s"]);
        $this->assertEquals(["Dave's"], $tokens);
    }
}
