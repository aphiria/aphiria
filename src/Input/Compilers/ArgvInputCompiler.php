<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Input\Compilers;

use Aphiria\Console\Input\Compilers\Tokenizers\ArgvInputTokenizer;
use Aphiria\Console\Input\Input;
use InvalidArgumentException;

/**
 * Defines the argv input compiler
 */
final class ArgvInputCompiler extends InputCompiler
{
    /** @var ArgvInputTokenizer The tokenizer to use */
    private $tokenizer;

    public function __construct()
    {
        $this->tokenizer = new ArgvInputTokenizer();
    }

    /**
     * @inheritdoc
     */
    public function compile($rawInput): Input
    {
        if ($rawInput === null) {
            $rawInput = $_SERVER['argv'];
        }

        if (!is_array($rawInput)) {
            throw new InvalidArgumentException(self::class . ' only accepts arrays as input');
        }

        $tokens = $this->tokenizer->tokenize($rawInput);

        return $this->compileTokens($tokens);
    }
}
