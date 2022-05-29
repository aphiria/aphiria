<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Formatters;

/**
 * Defines the options for the padding formatter
 */
class PaddingFormatterOptions
{
    /**
     * @param string $paddingString The padding string
     * @param bool $padAfter Whether or not to pad after the string
     * @param string $eolChar The end-of-line character
     */
    public function __construct(
        public readonly string $paddingString = ' ',
        public readonly bool $padAfter = true,
        public readonly string $eolChar = PHP_EOL
    ) {
    }
}
