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

use Closure;

/**
 * Defines the padding formatter
 */
class PaddingFormatter
{
    /**
     * @param PaddingFormatterOptions $defaultOptions The default options to use
     */
    public function __construct(private readonly PaddingFormatterOptions $defaultOptions = new PaddingFormatterOptions())
    {
    }

    /**
     * Formats rows of text so that each column is the same width
     *
     * @param array<int, mixed> $rows The rows to pad
     * @param Closure(array<mixed>): string $callback The callback that returns a formatted row of text
     * @param PaddingFormatterOptions|null $options The options to use, or null if using the default ones
     * @return string A list of formatted rows
     */
    public function format(array $rows, Closure $callback, ?PaddingFormatterOptions $options = null): string
    {
        $options ??= $this->defaultOptions;
        // Normalize all rows to be an array
        $numRows = \count($rows);

        for ($rowIndex = 0;$rowIndex < $numRows;$rowIndex++) {
            $rows[$rowIndex] = (array)$rows[$rowIndex];
        }

        /** @var array<int, array> $rows */
        $maxLengths = $this->normalizeColumns($rows);
        $paddingType = $options->padAfter ? STR_PAD_RIGHT : STR_PAD_LEFT;
        $numRows = \count($rows);

        // Format the rows
        for ($rowIndex = 0;$rowIndex < $numRows;$rowIndex++) {
            /** @psalm-suppress MixedAssignment Each item could be a mixed value */
            foreach ($rows[$rowIndex] as $itemIndex => $item) {
                // The formatting tags will be compiled and disappear from the string
                // So, we want additional padding equal to the length of the formatting so that the widths come out right
                $formattingWidth = $this->getStringWidth((string)$item, true) - $this->getStringWidth((string)$item, false);
                $rows[$rowIndex][$itemIndex] = \str_pad((string)$item, $maxLengths[(int)$itemIndex] + $formattingWidth, $options->paddingString, $paddingType);
            }
        }

        $formattedText = '';

        for ($rowIndex = 0;$rowIndex < $numRows;$rowIndex++) {
            $formattedText .= $callback($rows[$rowIndex]) . $options->eolChar;
        }

        // Trim the excess separator
        return \preg_replace('/' . \preg_quote($options->eolChar, '/') . '$/', '', $formattedText);
    }

    /**
     * Normalizes the number of columns in each row
     *
     * @param array<int, array> $rows The rows to equalize
     * @return array<int, int> The max length of each column
     */
    public function normalizeColumns(array &$rows): array
    {
        $maxNumColumns = 0;

        // Find the max number of columns that appear in any given row
        foreach ($rows as $row) {
            $maxNumColumns = \max($maxNumColumns, \count($row));
        }

        $maxLengths = \array_pad([], $maxNumColumns, 0);

        // Normalize the number of columns in each row
        $numRows = \count($rows);

        for ($rowIndex = 0;$rowIndex < $numRows;$rowIndex++) {
            $rows[$rowIndex] = \array_pad($rows[$rowIndex], $maxNumColumns, '');
        }

        // Get the length of the longest value in each column
        for ($rowIndex = 0;$rowIndex < $numRows;$rowIndex++) {
            /** @psalm-suppress MixedAssignment The value could be a mixed type */
            foreach ($rows[$rowIndex] as $columnIndex => $value) {
                $rows[$rowIndex][$columnIndex] = \trim((string)$value);
                $maxLengths[$columnIndex] = \max($maxLengths[$columnIndex], $this->getStringWidth($rows[$rowIndex][$columnIndex], false));
            }
        }

        /** @var array<int, int> $maxLengths */
        return $maxLengths;
    }

    /**
     * Gets the width of a string
     *
     * @param string $string The string whose width we want
     * @param bool $includeFormatting Whether or not to include any uncompiled formatting tags
     * @return int The string width
     */
    private function getStringWidth(string $string, bool $includeFormatting): int
    {
        if ($includeFormatting) {
            return \mb_strwidth($string);
        }

        return \mb_strwidth(\strip_tags($string));
    }
}
