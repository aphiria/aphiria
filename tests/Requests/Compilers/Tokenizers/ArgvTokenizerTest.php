<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Requests\Compilers\Tokenizers;

use Aphiria\Console\Requests\Compilers\Tokenizers\ArgvTokenizer;
use PHPUnit\Framework\TestCase;

    /**
 * Tests the argv tokenizer
 */
class ArgvTokenizerTest extends TestCase
{
    /** @var ArgvTokenizer The tokenizer to use in tests */
    private $tokenizer;

    public function setUp(): void
    {
        $this->tokenizer = new ArgvTokenizer();
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
