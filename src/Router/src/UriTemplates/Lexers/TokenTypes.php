<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Lexers;

/**
 * Defines the various token types
 */
final class TokenTypes
{
    /** @const A text token type */
    public const T_TEXT = 'T_TEXT';
    /** @const A number token type */
    public const T_NUMBER = 'T_NUMBER';
    /** @const A punctuation token type */
    public const T_PUNCTUATION = 'T_PUNCTUATION';
    /** @const A quoted string token type */
    public const T_QUOTED_STRING = 'T_QUOTED_STRING';
    /** @const A variable token type */
    public const T_VARIABLE = 'T_VARIABLE';
}
