<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\UriTemplates\Compilers\Parsers;

use InvalidArgumentException;
use Opulence\Routing\Matchers\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenStream;

/**
 * Defines the interface for URI template parsers to implement
 */
interface IUriTemplateParser
{
    /**
     * Parses a token stream from a URI template into an abstract syntax tree
     *
     * @param TokenStream $tokens The stream of tokens to parse
     * @return AbstractSyntaxTree The parsed abstract syntax tree
     * @throws InvalidArgumentException Thrown if the token stream is invalid
     */
    public function parse(TokenStream $tokens) : AbstractSyntaxTree;
}
