<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Input\Compilers;

use Aphiria\Console\Input\Compilers\Tokenizers\ArrayListInputTokenizer;
use Aphiria\Console\Input\Input;
use InvalidArgumentException;

/**
 * Defines the array list input compiler
 */
final class ArrayListInputCompiler extends InputCompiler
{
    /** @var ArrayListInputTokenizer The tokenizer to use */
    private $tokenizer;

    public function __construct()
    {
        $this->tokenizer = new ArrayListInputTokenizer();
    }

    /**
     * @inheritdoc
     */
    public function compile($rawInput): Input
    {
        if (!is_array($rawInput)) {
            throw new InvalidArgumentException(self::class . ' only accepts arrays as input');
        }

        $tokens = $this->tokenizer->tokenize($rawInput);

        return $this->compileTokens($tokens);
    }
}
