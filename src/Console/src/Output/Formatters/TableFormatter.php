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
 * Defines a table formatter
 */
class TableFormatter
{
    /**
     * @param TableFormatterOptions $defaultOptions The default options to use
     * @param PaddingFormatter $padding The padding formatter
     */
    public function __construct(
        private readonly TableFormatterOptions $defaultOptions = new TableFormatterOptions(),
        private readonly PaddingFormatter $padding = new PaddingFormatter()
    ) {
    }

    /**
     * Formats the table into a string
     *
     * @param array<int, mixed> $rows The list of rows
     * @param list<mixed> $headers The list of headers
     * @param TableFormatterOptions|null $options The options to use, or null if using the default options
     * @return string The formatted table
     */
    public function format(array $rows, array $headers = [], TableFormatterOptions $options = null): string
    {
        $options ??= $this->defaultOptions;
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
        $rowText = \explode(
            $options->eolChar,
            $this->padding->format(
                $headersAndRows,
                fn (array $row): string => \sprintf(
                    '%s%s%s%s%s',
                    $options->verticalBorderChar,
                    $options->cellPaddingString,
                    \implode($options->cellPaddingString . $options->verticalBorderChar . $options->cellPaddingString, \array_map(static fn (mixed $value) => (string)$value, $row)),
                    $options->cellPaddingString,
                    $options->verticalBorderChar
                ),
                new PaddingFormatterOptions($options->cellPaddingString, $options->padAfter, $options->eolChar)
            )
        );

        // Create the borders
        $borders = [];

        foreach ($maxLengths as $maxLength) {
            $borders[] = \str_repeat($options->horizontalBorderChar, $maxLength + 2 * \mb_strlen($options->cellPaddingString));
        }

        $borderText = $options->intersectionChar . \implode($options->intersectionChar, $borders) . $options->intersectionChar;
        $headerText = \count($headers) > 0 ? \array_shift($rowText) . $options->eolChar . $borderText . $options->eolChar : '';

        return $borderText . $options->eolChar . $headerText . \implode($options->eolChar, $rowText) . $options->eolChar . $borderText;
    }
}
