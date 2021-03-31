<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
     * @param string|array<array-key, mixed> $input The input to tokenize
     * @return list<mixed> The list of tokens
     * @throws InvalidArgumentException Thrown if the input was invalid
     */
    public function tokenize(string|array $input): array;
}
