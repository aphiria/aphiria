<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Input\Compilers;

use Aphiria\Console\Input\Compilers\Tokenizers\StringInputTokenizer;
use Aphiria\Console\Input\Input;

/**
 * Defines the string input compiler
 */
final class StringInputCompiler extends InputCompiler
{
    /** @var StringInputTokenizer The tokenizer to use */
    private $tokenizer;

    public function __construct()
    {
        $this->tokenizer = new StringInputTokenizer();
    }

    /**
     * @inheritdoc
     */
    public function compile($rawInput): Input
    {
        $tokens = $this->tokenizer->tokenize($rawInput);

        return $this->compileTokens($tokens);
    }
}
