<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Parsers;

use Aphiria\Console\Output\Lexers\OutputToken;
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
     * @return AstNode The abstract syntax tree made from the tokens
     * @throws RuntimeException Thrown if there was an error in the tokens
     */
    public function parse(array $tokens): AstNode;
}
