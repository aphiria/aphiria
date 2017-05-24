<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\UriTemplates\Compilers\Parsers\Lexers\Tokens;

/**
 * Defines the various token types
 */
class TokenTypes
{
    /** @var string A text token type */
    public const T_TEXT = 'T_TEXT';
    /** @var string A number token type */
    public const T_NUMBER = 'T_NUMBER';
    /** @var string A punctuation token type */
    public const T_PUNCTUATION = 'T_PUNCTUATION';
    /** @var string A quoted string token type */
    public const T_QUOTED_STRING = 'T_QUOTED_STRING';
    /** @var string A variable token type */
    public const T_VARIABLE = 'T_VARIABLE';
}
