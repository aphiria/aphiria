<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Responses\Compilers\Lexers\Tokens;

/**
 * Defines the different token types
 */
class TokenTypes
{
    /** Defines an unknown token type */
    public const T_UNKNOWN = 'T_UNKNOWN';
    /** Defines an end of file token type */
    public const T_EOF = 'T_EOF';
    /** Defines a word token type */
    public const T_WORD = 'T_WORD';
    /** Defines an open tag token type */
    public const T_TAG_OPEN = 'T_TAG_OPEN';
    /** Defines a close tag token type */
    public const T_TAG_CLOSE = 'T_TAG_CLOSE';
}
