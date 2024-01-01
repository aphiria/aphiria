<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Formatters;

use InvalidArgumentException;

/**
 * Defines the options for the padding formatter
 */
readonly class PaddingFormatterOptions
{
    /**
     * @param string $paddingString The padding string
     * @param bool $padAfter Whether or not to pad after the string
     * @param non-empty-string $eolChar The end-of-line character
     */
    public function __construct(
        public string $paddingString = ' ',
        public bool $padAfter = true,
        public string $eolChar = PHP_EOL
    ) {
        if (empty($this->eolChar)) {
            throw new InvalidArgumentException('EOL character cannot be empty');
        }
    }
}
