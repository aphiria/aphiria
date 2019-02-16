<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Requests\Tokenizers;

use Aphiria\Console\Requests\Tokenizers\ArgvTokenizer;
use PHPUnit\Framework\TestCase;

/**
 * Tests the argv tokenizer
 */
class ArgvTokenizerTest extends TestCase
{
    /** @var ArgvTokenizer The tokenizer to use in tests */
    private $tokenizer;

    /**
     * Sets up the tests
     */
    public function setUp(): void
    {
        $this->tokenizer = new ArgvTokenizer();
    }

    /**
     * Tests tokenizing an escaped double quote
     */
    public function testTokenizingEscapedDoubleQuote(): void
    {
        $tokens = $this->tokenizer->tokenize(['foo', 'Dave\"s']);
        $this->assertEquals(['Dave"s'], $tokens);
    }

    /**
     * Tests tokenizing an escaped single quote
     */
    public function testTokenizingEscapedSingleQuote(): void
    {
        $tokens = $this->tokenizer->tokenize(['foo', "Dave\'s"]);
        $this->assertEquals(["Dave's"], $tokens);
    }
}
