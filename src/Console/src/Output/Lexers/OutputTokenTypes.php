<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Lexers;

/**
 * Defines the different output token types
 */
final class OutputTokenTypes
{
    /** Defines an end of file token type */
    public const T_EOF = 'T_EOF';
    /** Defines a word token type */
    public const T_WORD = 'T_WORD';
    /** Defines an open tag token type */
    public const T_TAG_OPEN = 'T_TAG_OPEN';
    /** Defines a close tag token type */
    public const T_TAG_CLOSE = 'T_TAG_CLOSE';
}
