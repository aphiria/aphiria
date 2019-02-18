<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses\Compilers\Lexers;

use Aphiria\Console\Responses\Compilers\Lexers\Tokens\Token;
use RuntimeException;

/**
 * Defines the interface for response lexers to implement
 */
interface IResponseLexer
{
    /**
     * Lexes input text and returns a list of tokens
     *
     * @param string $text The text to lex
     * @return Token[] The list of tokens
     * @throws RuntimeException Thrown if there was an error lexing the text
     */
    public function lex(string $text): array;
}
