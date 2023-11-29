<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Formatters;

/**
 * Defines the options for the progress bar formatter
 */
class ProgressBarFormatterOptions
{
    /** @const The default width of the progress bar (including delimiters) */
    private const int DEFAULT_PROGRESS_BAR_WIDTH = 80;

    /**
     * @param int $progressBarWidth The progress bar width (including delimiters)
     * @param string $outputFormat The output format to use
     *      Acceptable placeholders are 'progress', 'maxSteps', 'bar', 'percent', and 'timeRemaining'
     * @param string $completedProgressChar The completed progress character
     * @param string $remainingProgressChar The remaining progress character
     * @param int $redrawFrequency The frequency in seconds we redraw the progress bar
     */
    public function __construct(
        public readonly int $progressBarWidth = self::DEFAULT_PROGRESS_BAR_WIDTH,
        public readonly string $outputFormat = '%bar% %progress%/%maxSteps%' . PHP_EOL . 'Time remaining: %timeRemaining%',
        public readonly string $completedProgressChar = '=',
        public readonly string $remainingProgressChar = '-',
        public readonly int $redrawFrequency = 1
    ) {
    }
}
