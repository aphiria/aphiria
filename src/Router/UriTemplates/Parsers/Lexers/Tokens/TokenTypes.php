<?php
namespace Opulence\Router\UriTemplates\Parsers\Lexers\Tokens;

/**
 * Defines the various token types
 */
class TokenTypes
{
    public const T_TEXT = 'T_TEXT';
    public const T_PUNCTUATION = 'T_PUNCTUATION';
    public const T_QUOTED_STRING = 'T_QUOTED_STRING';
    public const T_VARIABLE = 'T_VARIABLE';
}
