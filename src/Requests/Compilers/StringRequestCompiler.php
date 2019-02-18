<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Requests\Compilers;

use Aphiria\Console\Requests\Request;
use Aphiria\Console\Requests\Compilers\Tokenizers\StringTokenizer;

/**
 * Defines the string compiler
 */
final class StringRequestCompiler extends RequestCompiler
{
    /** @var StringTokenizer The tokenizer to use */
    private $tokenizer;

    public function __construct()
    {
        $this->tokenizer = new StringTokenizer();
    }

    /**
     * @inheritdoc
     */
    public function compile($input): Request
    {
        $tokens = $this->tokenizer->tokenize($input);

        return $this->compileTokens($tokens);
    }
}
