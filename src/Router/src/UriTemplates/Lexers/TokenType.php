<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\UriTemplates\Lexers;

/**
 * Defines the various token types
 */
enum TokenType
{
    /** A text token type */
    case Text;
    /** A number token type */
    case Number;
    /** A punctuation token type */
    case Punctuation;
    /** A quoted string token type */
    case QuotedString;
    /** A variable token type */
    case Variable;
    /** The end of file token type */
    case Eof;
}
