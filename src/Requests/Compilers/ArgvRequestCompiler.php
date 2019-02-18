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
use Aphiria\Console\Requests\Compilers\Tokenizers\ArgvTokenizer;
use InvalidArgumentException;

/**
 * Defines the argv compiler
 */
final class ArgvRequestCompiler extends RequestCompiler
{
    /** @var ArgvTokenizer The tokenizer to use */
    private $tokenizer;

    public function __construct()
    {
        $this->tokenizer = new ArgvTokenizer();
    }

    /**
     * @inheritdoc
     */
    public function compile($input): Request
    {
        if ($input === null) {
            $input = $_SERVER['argv'];
        }

        if (!is_array($input)) {
            throw new InvalidArgumentException(self::class . ' only accepts arrays as input');
        }

        $tokens = $this->tokenizer->tokenize($input);

        return $this->compileTokens($tokens);
    }
}
