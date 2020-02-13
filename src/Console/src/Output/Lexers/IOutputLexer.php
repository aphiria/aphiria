<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Lexers;

use RuntimeException;

/**
 * Defines the interface for output lexers to implement
 */
interface IOutputLexer
{
    /**
     * Lexes output text and returns a list of tokens
     *
     * @param string $text The text to lex
     * @return OutputToken[] The list of tokens
     * @throws RuntimeException Thrown if there was an error lexing the text
     */
    public function lex(string $text): array;
}
