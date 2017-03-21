<?php
namespace Opulence\Router\UriTemplates\Compilers\Parsers\Lexers;

use InvalidArgumentException;
use Opulence\Router\UriTemplates\Compilers\Parsers\Lexers\Tokens\TokenStream;

/**
 * Defines the interface for URI template lexers to implement
 */
interface IUriTemplateLexer
{
    /**
     * Lexes a raw template into a stream of tokens
     *
     * @param string $template The raw template to lex
     * @return TokenStream The stream of lexed tokens
     * @throws InvalidArgumentException Thrown if the template was incorrectly formatted
     */
    public function lex(string $template) : TokenStream;
}
