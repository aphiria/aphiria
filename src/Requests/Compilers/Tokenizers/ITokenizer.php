<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Requests\Compilers\Tokenizers;

/**
 * Defines the interface for tokenizers to implement
 */
interface ITokenizer
{
    /**
     * Tokenizes a request string
     *
     * @param mixed $input The input to tokenize
     * @return array The list of tokens
     */
    public function tokenize($input): array;
}
