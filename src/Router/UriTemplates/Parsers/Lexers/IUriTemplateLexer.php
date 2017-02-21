<?php
namespace Opulence\Router\UriTemplates\Parsers\Lexers;

use Opulence\Router\UriTemplates\Parsers\Lexers\Token;

/**
 * Defines the interface for URI template lexers to implement
 */
interface IUriTemplateLexer
{
    /**
     * Lexes a raw template into a stream of tokens
     * 
     * @param string $template The raw template to lex
     * @return Token[] The stream of lexed tokens
     */
    public function lex(string $template) : array;
}
