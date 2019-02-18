<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Output\Compilers\Parsers\Lexers;

use Aphiria\Console\Output\Compilers\Parsers\Lexers\Tokens\OutputToken;
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
