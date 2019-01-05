<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\UriTemplates\Parsers;

use InvalidArgumentException;
use Opulence\Routing\UriTemplates\Parsers\Lexers\TokenStream;

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
    public function parse(TokenStream $tokens) : AstNode;
}
