<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Input\Tokenizers;

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
