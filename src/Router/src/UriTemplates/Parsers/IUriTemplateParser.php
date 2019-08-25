<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Parsers;

use Aphiria\Routing\UriTemplates\Parsers\Lexers\TokenStream;
use InvalidArgumentException;

/**
 * Defines the interface for URI template parsers to implement
 */
interface IUriTemplateParser
{
    /**
     * Parses a token stream from a URI template into an abstract syntax tree
     *
     * @param TokenStream $tokens The stream of tokens to parse
     * @return AstNode The parsed abstract syntax tree
     * @throws InvalidArgumentException Thrown if the token stream is invalid
     */
    public function parse(TokenStream $tokens): AstNode;
}
