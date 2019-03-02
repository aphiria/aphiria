<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Input\Compilers\Tokenizers;

use InvalidArgumentException;

/**
 * Defines the interface for input tokenizers to implement
 */
interface IInputTokenizer
{
    /**
     * Tokenizes an input string
     *
     * @param string|array $input The input to tokenize
     * @return array The list of tokens
     * @throws InvalidArgumentException Thrown if the input was invalid
     */
    public function tokenize($input): array;
}
