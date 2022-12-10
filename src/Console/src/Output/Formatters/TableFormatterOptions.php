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
 * Defines the options for the table formatter
 */
readonly class TableFormatterOptions
{
    /**
     * @param string $cellPaddingString The cell padding string
     * @param string $verticalBorderChar The character to use for vertical borders
     * @param string $horizontalBorderChar The character to use for horizontal borders
     * @param string $intersectionChar The character to use for row/column intersections
     * @param bool $padAfter Whether or not to pad after a string
     * @param string $eolChar The end-of-line character
     * @see PaddingFormatterOptions The EOL char and whether we pad after should be kept identical between the two options classes
     */
    public function __construct(
        public string $cellPaddingString = ' ',
        public string $verticalBorderChar = '|',
        public string $horizontalBorderChar = '-',
        public string $intersectionChar = '+',
        public bool $padAfter = true,
        public string $eolChar = PHP_EOL
    ) {
    }
}
