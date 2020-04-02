<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
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
     * @param array $rows The rows to pad
     * @param callable $callback The callback that returns a formatted row of text
     * @return string A list of formatted rows
     */
    public function format(array $rows, callable $callback): string
    {
        foreach ($rows as $rowIndex => $row) {
            $rows[$rowIndex] = (array)$rows[$rowIndex];
        }

        $maxLengths = $this->normalizeColumns($rows);
        $paddingType = $this->padAfter ? STR_PAD_RIGHT : STR_PAD_LEFT;

        // Format the rows
        foreach ($rows as $rowIndex => $row) {
            foreach ($rows[$rowIndex] as $itemIndex => $item) {
                $rows[$rowIndex][$itemIndex] = str_pad($item, $maxLengths[$itemIndex], $this->paddingString, $paddingType);
            }
        }

        $formattedText = '';

        foreach ($rows as $rowIndex => $row) {
            $formattedText .= $callback($rows[$rowIndex]) . $this->eolChar;
        }

        // Trim the excess separator
        $formattedText = preg_replace('/' . preg_quote($this->eolChar, '/') . '$/', '', $formattedText);

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
     * @param array $rows The rows to equalize
     * @return array The max length of each column
     */
    public function normalizeColumns(array &$rows): array
    {
        $maxNumColumns = 0;

        // Find the max number of columns that appear in any given row
        foreach ($rows as $row) {
            $maxNumColumns = max($maxNumColumns, \count($row));
        }

        $maxLengths = array_pad([], $maxNumColumns, 0);

        // Normalize the number of columns in each row
        foreach ($rows as $rowIndex => $row) {
            $rows[$rowIndex] = array_pad($rows[$rowIndex], $maxNumColumns, '');
        }

        // Get the length of the longest value in each column
        foreach ($rows as $rowIndex => $row) {
            foreach ($rows[$rowIndex] as $columnIndex => $value) {
                $rows[$rowIndex][$columnIndex] = trim($value);
                $maxLengths[$columnIndex] = max($maxLengths[$columnIndex], mb_strlen($rows[$rowIndex][$columnIndex]));
            }
        }

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
