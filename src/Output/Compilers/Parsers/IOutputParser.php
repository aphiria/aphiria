<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Output\Compilers\Parsers;

use Aphiria\Console\Output\Compilers\Parsers\Lexers\Tokens\OutputToken;
use RuntimeException;

/**
 * Defines the interface for output parsers to implement
 */
interface IOutputParser
{
    /**
     * Parses tokens into an abstract syntax tree
     *
     * @param OutputToken[] $tokens The list of tokens to parse
     * @return AbstractSyntaxTree The abstract syntax tree made from the tokens
     * @throws RuntimeException Thrown if there was an error in the tokens
     */
    public function parse(array $tokens): AbstractSyntaxTree;
}
