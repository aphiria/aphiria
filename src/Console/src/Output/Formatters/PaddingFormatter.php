<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Formatters;

/**
 * Defines the padding formatter
 */
class PaddingFormatter
{
    /** @var bool Whether or not to pad after the string */
    private bool $padAfter = true;
    /** @var string The padding string */
    private string $paddingString = ' ';
    /** @var string The end-of-line character */
    private string $eolChar = PHP_EOL;

    /**
     * Formats rows of text so that each column is the same width
     *
     * @param array<int, mixed> $rows The rows to pad
     * @param callable(array<mixed>): string $callback The callback that returns a formatted row of text
     * @return string A list of formatted rows
     */
    public function format(array $rows, callable $callback): string
    {
        // Normalize all rows to be an array
        $numRows = \count($rows);

        /** @psalm-suppress MixedAssignment Each row could be a mixed value */
        for ($rowIndex = 0;$rowIndex < $numRows;$rowIndex++) {
            $rows[$rowIndex] = (array)$rows[$rowIndex];
        }

        /** @var array<int, array> $rows */
        $maxLengths = $this->normalizeColumns($rows);
        $paddingType = $this->padAfter ? STR_PAD_RIGHT : STR_PAD_LEFT;
        $numRows = \count($rows);

        // Format the rows
        for ($rowIndex = 0;$rowIndex < $numRows;$rowIndex++) {
            /** @psalm-suppress MixedAssignment Each item could be a mixed value */
            foreach ($rows[$rowIndex] as $itemIndex => $item) {
                $rows[$rowIndex][$itemIndex] = \str_pad((string)$item, $maxLengths[(int)$itemIndex], $this->paddingString, $paddingType);
            }
        }

        $formattedText = '';

        for ($rowIndex = 0;$rowIndex < $numRows;$rowIndex++) {
            $formattedText .= $callback($rows[$rowIndex]) . $this->eolChar;
        }

        // Trim the excess separator
        $formattedText = \preg_replace('/' . \preg_quote($this->eolChar, '/') . '$/', '', $formattedText);

        return $formattedText;
    }

    /**
     * Gets the EOL character
     *
     * @return string The end-of-line character
     */
    public function getEolChar(): string
    {
        return $this->eolChar;
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
                $maxLengths[$columnIndex] = \max($maxLengths[$columnIndex], \mb_strlen($rows[$rowIndex][$columnIndex]));
            }
        }

        /** @var array<int, int> $maxLengths */
        return $maxLengths;
    }

    /**
     * Sets the EOL character
     *
     * @param string $eolChar The new end-of-line character
     */
    public function setEolChar(string $eolChar): void
    {
        $this->eolChar = $eolChar;
    }

    /**
     * Sets whether or not we pad after or before
     *
     * @param bool $padAfter True if we want to pad after, otherwise false and we'll pad before
     */
    public function setPadAfter(bool $padAfter): void
    {
        $this->padAfter = $padAfter;
    }

    /**
     * Sets the padding string
     *
     * @param string $paddingString The string to use for padding
     */
    public function setPaddingString(string $paddingString): void
    {
        $this->paddingString = $paddingString;
    }
}
