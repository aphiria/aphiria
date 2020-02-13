<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Parsers;

use Aphiria\Routing\UriTemplates\Lexers\TokenStream;
use Aphiria\Routing\UriTemplates\Lexers\UnexpectedTokenException;

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
     * @throws UnexpectedTokenException Thrown if the token stream is invalid
     */
    public function parse(TokenStream $tokens): AstNode;
}
