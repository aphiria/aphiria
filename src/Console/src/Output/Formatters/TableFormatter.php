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
 * Defines a table formatter
 */
class TableFormatter
{
    /** @var string The padding string */
    private string $cellPaddingString = ' ';
    /** @var string The character to use for vertical borders */
    private string $verticalBorderChar = '|';
    /** @var string The character to use for horizontal borders */
    private string $horizontalBorderChar = '-';
    /** @var string The character to use for row/column intersections */
    private string $intersectionChar = '+';

    /**
     * @param PaddingFormatter $padding The padding formatter
     */
    public function __construct(private readonly PaddingFormatter $padding = new PaddingFormatter())
    {
    }

    /**
     * Formats the table into a string
     *
     * @param array<int, mixed> $rows The list of rows
     * @param list<mixed> $headers The list of headers
     * @return string The formatted table
     */
    public function format(array $rows, array $headers = []): string
    {
        $numRows = \count($rows);

        if ($numRows === 0) {
            return '';
        }

        // Normalize all rows to be an array
        for ($rowIndex = 0;$rowIndex < $numRows;$rowIndex++) {
            $rows[$rowIndex] = (array)$rows[$rowIndex];
        }

        // If there are headers, we want them to be formatted along with the rows
        /** @var array<int, array> $headersAndRows */
        $headersAndRows = \count($headers) === 0 ? $rows : [...[$headers], ...$rows];
        $maxLengths = $this->padding->normalizeColumns($headersAndRows);
        $eolChar = $this->padding->eolChar;
        $rowText = \explode(
            $eolChar,
            $this->padding->format(
                $headersAndRows,
                fn (array $row): string => \sprintf(
                    '%s%s%s%s%s',
                    $this->verticalBorderChar,
                    $this->cellPaddingString,
                    \implode($this->cellPaddingString . $this->verticalBorderChar . $this->cellPaddingString, \array_map(static fn (mixed $value) => (string)$value, $row)),
                    $this->cellPaddingString,
                    $this->verticalBorderChar
                )
            )
        );

        // Create the borders
        $borders = [];

        foreach ($maxLengths as $maxLength) {
            $borders[] = \str_repeat($this->horizontalBorderChar, $maxLength + 2 * \mb_strlen($this->cellPaddingString));
        }

        $borderText = $this->intersectionChar . \implode($this->intersectionChar, $borders) . $this->intersectionChar;
        $headerText = \count($headers) > 0 ? \array_shift($rowText) . $eolChar . $borderText . $eolChar : '';

        return $borderText . $eolChar . $headerText . \implode($eolChar, $rowText) . $eolChar . $borderText;
    }

    /**
     * @param string $cellPaddingString
     */
    public function setCellPaddingString(string $cellPaddingString): void
    {
        $this->cellPaddingString = $cellPaddingString;
    }

    /**
     * @param string $eolChar
     */
    public function setEolChar(string $eolChar): void
    {
        $this->padding->eolChar = $eolChar;
    }

    /**
     * @param string $horizontalBorderChar
     */
    public function setHorizontalBorderChar(string $horizontalBorderChar): void
    {
        $this->horizontalBorderChar = $horizontalBorderChar;
    }

    /**
     * @param string $intersectionChar
     */
    public function setIntersectionChar(string $intersectionChar): void
    {
        $this->intersectionChar = $intersectionChar;
    }

    /**
     * @param bool $padAfter
     */
    public function setPadAfter(bool $padAfter): void
    {
        $this->padding->setPadAfter($padAfter);
    }

    /**
     * @param string $verticalBorderChar
     */
    public function setVerticalBorderChar(string $verticalBorderChar): void
    {
        $this->verticalBorderChar = $verticalBorderChar;
    }
}
