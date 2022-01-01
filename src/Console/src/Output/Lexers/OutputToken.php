<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Lexers;

/**
 * Defines a output token
 */
final class OutputToken
{
    /**
     * @param OutputTokenType $type The token type
     * @param mixed $value The value of the token
     * @param int $position The position of the token in the original text
     */
    public function __construct(
        public readonly OutputTokenType $type,
        public readonly mixed $value,
        public readonly int $position
    ) {
    }
}
