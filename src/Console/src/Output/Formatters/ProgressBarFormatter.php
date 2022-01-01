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

use Aphiria\Console\Output\IOutput;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

/**
 * Defines the formatter for progress bars
 */
class ProgressBarFormatter implements IProgressBarObserver
{
    /** @const The default width of the progress bar (including delimiters) */
    private const DEFAULT_PROGRESS_BAR_WIDTH = 80;
    /** @var string The completed progress character */
    public string $completedProgressChar = '=';
    /** @var string The remaining progress character */
    public string $remainingProgressChar = '-';
    /** @var DateTimeImmutable The start time of the progress bar */
    private readonly DateTimeImmutable $startTime;
    /** @var bool Whether or not this is the first time we've output the progress bar */
    private bool $isFirstOutput = true;

    /**
     * @param IOutput $output The output to draw to
     * @param int $progressBarWidth The width of the progress bar (including delimiters)
     * @param string $outputFormat The output format to use
     *      Acceptable placeholders are 'progress', 'maxSteps', 'bar', 'percent', and 'timeRemaining'
     * @param int $redrawFrequency The frequency in seconds we redraw the progress bar
     * @throws InvalidArgumentException Thrown if the max steps are invalid
     * @throws Exception Thrown if we could not create the start time
     */
    public function __construct(
        private readonly IOutput $output,
        private readonly int $progressBarWidth = self::DEFAULT_PROGRESS_BAR_WIDTH,
        private readonly string $outputFormat = '%bar% %progress%/%maxSteps%' . PHP_EOL . 'Time remaining: %timeRemaining%',
        private readonly int $redrawFrequency = 1
    ) {
        $this->startTime = new DateTimeImmutable();
    }

    /**
     * @inheritdoc
     */
    public function onProgressChanged(?int $prevProgress, int $currProgress, int $maxSteps): void
    {
        /**
         * Only redraw if we've completed the progress, we've made our first progress, or if it has been at least the
         * redraw frequency since the last progress
         */
        $shouldRedraw = $currProgress === $maxSteps
            || ($prevProgress === null)
            || $this->redrawFrequency === 0
            || \floor($currProgress / $this->redrawFrequency) !== \floor(($prevProgress) / $this->redrawFrequency);

        if ($shouldRedraw) {
            $this->output->write($this->compileOutput($currProgress, $maxSteps));
        }

        // Give ourselves a new line if the progress bar is finished
        if ($currProgress === $maxSteps) {
            $this->output->writeln('');
        }
    }

    /**
     * Compiles the bar itself
     *
     * @param int $progress The current progress
     * @param int $maxSteps The max steps that can be taken
     * @return string The bar
     */
    protected function compileBar(int $progress, int $maxSteps): string
    {
        if ($progress === $maxSteps) {
            // Don't show the percentage anymore
            $completedProgressString = \str_repeat($this->completedProgressChar, $this->progressBarWidth - 2);
            $progressLeftString = '';
        } else {
            $percentCompleteString = $this->compilePercent($progress, $maxSteps);
            $completedProgressString = \str_repeat(
                $this->completedProgressChar,
                (int)\max(0, \floor($progress / $maxSteps * ($this->progressBarWidth - 2) - \strlen($percentCompleteString)))
            ) . $percentCompleteString;
            $progressLeftString = \str_repeat(
                $this->remainingProgressChar,
                \max(0, $this->progressBarWidth - 2 - \strlen($completedProgressString))
            );
        }

        return "[$completedProgressString$progressLeftString]";
    }

    /**
     * Compiles the output for display
     *
     * @param int $progress The current progress
     * @param int $maxSteps The max steps that can be taken
     * @return string The formatted output string
     */
    protected function compileOutput(int $progress, int $maxSteps): string
    {
        // Before sending the output through sprintf(), we have to encode literal '%' by replacing them with '%%'
        $compiledOutput = \str_replace(
            ['%progress%', '%maxSteps%', '%bar%', '%timeRemaining%', '%percent%', '%'],
            [
                $progress,
                $maxSteps,
                $this->compileBar($progress, $maxSteps),
                $this->compileTimeRemaining($progress, $maxSteps),
                $this->compilePercent($progress, $maxSteps),
                '%%'
            ],
            $this->outputFormat
        );

        if ($this->isFirstOutput) {
            $this->isFirstOutput = false;

            // Still use sprintf() because there's some formatted strings in the output
            return \sprintf($compiledOutput, '');
        }

        // Clear previous output
        $newLineCount = \substr_count($this->outputFormat, PHP_EOL);

        return \sprintf("\033[2K\033[0G\033[{$newLineCount}A\033[2K$compiledOutput", '', '');
    }

    /**
     * Compiles the current percent complete
     *
     * @param int $progress The current progress
     * @param int $maxSteps The max steps that can be taken
     * @return string The compiled percent
     */
    protected function compilePercent(int $progress, int $maxSteps): string
    {
        return \floor(100 * $progress / $maxSteps) . '%';
    }

    /**
     * Compiles the estimated time remaining
     *
     * @param int $progress The current progress
     * @param int $maxSteps The max steps that can be taken
     * @return string The estimated time remaining
     */
    protected function compileTimeRemaining(int $progress, int $maxSteps): string
    {
        if ($progress === 0) {
            // We cannot estimate the time remaining if no progress has been made
            return 'Estimating...';
        }

        if ($progress === $maxSteps) {
            return 'Complete';
        }

        $secondsRemaining = $this->getSecondsRemaining($progress, $maxSteps);
        $timeFormats = [
            [0, 'less than 1 sec'],
            [1, '1 sec'],
            [2, 'secs', 1],
            [60, '1 min'],
            [120, 'mins', 60],
            [3600, '1 hr'],
            [7200, 'hrs', 3600],
            [86400, '1 day'],
            [172800, 'days', 86400],
        ];

        foreach ($timeFormats as $index => $timeFormat) {
            if ($secondsRemaining >= $timeFormat[0]) {
                if ((isset($timeFormats[$index + 1]) && $secondsRemaining < $timeFormats[$index + 1][0])
                    || $index === \count($timeFormats) - 1
                ) {
                    if (\count($timeFormat) === 2) {
                        return $timeFormat[1];
                    }

                    /** @psalm-suppress PossiblyUndefinedArrayOffset In this case, the array length will be 3, so we're good */
                    return \floor($secondsRemaining / $timeFormat[2]) . ' ' . $timeFormat[1];
                }
            }
        }

        return 'Estimating...';
    }

    /**
     * Gets the number of seconds remaining
     *
     * @param int $progress The current progress
     * @param int $maxSteps The max number of steps
     * @return float The number of seconds remaining
     */
    protected function getSecondsRemaining(int $progress, int $maxSteps): float
    {
        $elapsedTime = \time() - $this->startTime->getTimestamp();

        return \round($elapsedTime * $maxSteps / $progress - $elapsedTime);
    }
}
