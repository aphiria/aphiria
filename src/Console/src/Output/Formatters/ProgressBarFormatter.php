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

use Aphiria\Console\Output\IOutput;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

/**
 * Defines the formatter for progress bars
 */
class ProgressBarFormatter implements IProgressBarObserver
{
    /** @var DateTimeImmutable The start time of the progress bar */
    private readonly DateTimeImmutable $startTime;
    /** @var bool Whether or not this is the first time we've output the progress bar */
    private bool $isFirstOutput = true;

    /**
     * @param IOutput $output The output to draw to
     * @throws InvalidArgumentException Thrown if the max steps are invalid
     * @throws Exception Thrown if we could not create the start time
     */
    public function __construct(private readonly IOutput $output)
    {
        $this->startTime = new DateTimeImmutable();
    }

    /**
     * @inheritdoc
     */
    public function onProgressChanged(?int $prevProgress, int $currProgress, int $maxSteps, ProgressBarFormatterOptions $options): void
    {
        /**
         * Only redraw if we've completed the progress, we've made our first progress, or if it has been at least the
         * redraw frequency since the last progress
         */
        $shouldRedraw = $currProgress === $maxSteps
            || ($prevProgress === null)
            || $options->redrawFrequency === 0
            || \floor($currProgress / $options->redrawFrequency) !== \floor(($prevProgress) / $options->redrawFrequency);

        if ($shouldRedraw) {
            $this->output->write($this->compileOutput($currProgress, $maxSteps, $options));
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
     * @param ProgressBarFormatterOptions $options The options to use
     * @return string The bar
     */
    protected function compileBar(int $progress, int $maxSteps, ProgressBarFormatterOptions $options): string
    {
        if ($progress === $maxSteps) {
            // Don't show the percentage anymore
            $completedProgressString = \str_repeat($options->completedProgressChar, $options->progressBarWidth - 2);
            $progressLeftString = '';
        } else {
            $percentCompleteString = $this->compilePercent($progress, $maxSteps);
            $completedProgressString = \str_repeat(
                $options->completedProgressChar,
                (int)\max(0, \floor($progress / $maxSteps * ($options->progressBarWidth - 2) - \strlen($percentCompleteString)))
            ) . $percentCompleteString;
            $progressLeftString = \str_repeat(
                $options->remainingProgressChar,
                \max(0, $options->progressBarWidth - 2 - \strlen($completedProgressString))
            );
        }

        return "[$completedProgressString$progressLeftString]";
    }

    /**
     * Compiles the output for display
     *
     * @param int $progress The current progress
     * @param int $maxSteps The max steps that can be taken
     * @param ProgressBarFormatterOptions $options The options to use
     * @return string The formatted output string
     */
    protected function compileOutput(int $progress, int $maxSteps, ProgressBarFormatterOptions $options): string
    {
        // Before sending the output through sprintf(), we have to encode literal '%' by replacing them with '%%'
        $compiledOutput = \str_replace(
            ['%progress%', '%maxSteps%', '%bar%', '%timeRemaining%', '%percent%', '%'],
            [
                $progress,
                $maxSteps,
                $this->compileBar($progress, $maxSteps, $options),
                $this->compileTimeRemaining($progress, $maxSteps),
                $this->compilePercent($progress, $maxSteps),
                '%%'
            ],
            $options->outputFormat
        );

        if ($this->isFirstOutput) {
            $this->isFirstOutput = false;

            // Still use sprintf() because there's some formatted strings in the output
            return \sprintf($compiledOutput, '');
        }

        // Clear previous output
        $newLineCount = \substr_count($options->outputFormat, PHP_EOL);

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
        /** @var list<array{0: int, 1: string}|array{0: int, 1: string, 2: int}> $timeFormats */
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
