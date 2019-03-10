<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Output\Formatters;

use Aphiria\Console\Output\IOutput;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;

/**
 * Defines a progress bar
 */
final class ProgressBar
{
    /** @const The width of the screen to fill */
    private const PROGRESS_BAR_WIDTH = 80;
    /** @var string The progress character */
    public $progressChar = '=';
    /** @var string The remaining progress character */
    public $remainingProgressChar = '-';
    /** @var IOutput The output to draw to */
    private $output;
    /** @var int The max number of steps */
    private $maxSteps;
    /** @var int The progress made */
    private $progress = 0;
    /** @var DateTimeImmutable The start time of the progress bar */
    private $startTime;
    /** @var string The output string format */
    private $outputFormat;
    /** @var int The frequency in seconds we redraw the progress bar */
    private $redrawFrequency = 1;
    /** @var bool Whether or not this is the first time we've output the progress bar */
    private $isFirstOutput = true;

    /**
     * @param IOutput $output The output to draw to
     * @param int $maxSteps The max number of steps
     * @param string|null $outputFormat The output format to use, or null if using the default
     *      Acceptable placeholders are 'progress', 'maxSteps', 'bar', and 'timeRemaining'
     * @throws InvalidArgumentException Thrown if the max steps are invalid
     * @throws Exception Thrown if there was an unhandled exception creating the start time
     */
    public function __construct(IOutput$output, int $maxSteps, string $outputFormat = null)
    {
        if ($maxSteps <= 0) {
            throw new InvalidArgumentException('Max steps must be greater than 0');
        }

        $this->output = $output;
        $this->maxSteps = $maxSteps;
        $this->outputFormat = $outputFormat ?? '%bar% %progress%/%maxSteps%' . PHP_EOL . 'Time remaining: %timeRemaining%';
        $this->startTime = new DateTimeImmutable();
    }

    /**
     * Advances the progress one step
     *
     * @param int $step The amount to step by
     * @throws Exception Thrown if there was an error writing the output
     */
    public function advance(int $step = 1): void
    {
        // Purposely call this so we benefit from the draw functionality
        $this->setProgress($this->progress + $step);
    }

    /**
     * Finishes advancing the progress bar
     *
     * @throws Exception Thrown if there was an error writing the output
     */
    public function finish(): void
    {
        // Purposely call this so we benefit from the draw functionality
        $this->setProgress($this->maxSteps);
    }

    /**
     * Sets the current progress
     *
     * @param int $progress The current progress
     * @throws Exception Thrown if there was an error writing the output
     */
    public function setProgress(int $progress): void
    {
        $shouldRedraw = $progress === $this->maxSteps
            || floor($progress / $this->redrawFrequency) !== floor($this->progress / $this->redrawFrequency);
        // Bound the progress between 0 and the max steps
        $this->progress = max(0, min($this->maxSteps, $progress));

        if ($shouldRedraw) {
            $this->output->write($this->formatOutput());
        }

        // Give ourselves a new line if the progress bar is finished
        if ($this->progress === $this->maxSteps) {
            $this->output->writeln('');
        }
    }

    /**
     * Formats the output for display
     *
     * @return string The formatted output string
     * @throws Exception Thrown if there was an error formatting the output
     */
    private function formatOutput(): string
    {
        if ($this->progress === $this->maxSteps) {
            // Don't show the percentage anymore
            $progressCompleteString = str_repeat($this->progressChar, self::PROGRESS_BAR_WIDTH - 2);
            $progressLeftString = '';
        } else {
            $percentComplete = floor(100 * $this->progress / $this->maxSteps);
            $paddedBarProgress = str_pad("$percentComplete%%", 3, $this->remainingProgressChar);
            $progressCompleteString = str_repeat(
                $this->progressChar,
                max(0, floor($this->progress / $this->maxSteps * (self::PROGRESS_BAR_WIDTH - 2) - strlen($paddedBarProgress)))
            ) . $paddedBarProgress;
            $progressLeftString = str_repeat(
                $this->remainingProgressChar,
                self::PROGRESS_BAR_WIDTH - 1 - strlen($progressCompleteString)
            );
        }

        $compiledOutput = str_replace(
            ['%progress%', '%maxSteps%', '%bar%', '%timeRemaining%'],
            [
                $this->progress,
                $this->maxSteps,
                '[' . $progressCompleteString . $progressLeftString . ']',
                $this->getEstimatedTimeRemaining()
            ],
            $this->outputFormat
        );

        if ($this->isFirstOutput) {
            $this->isFirstOutput = false;

            // Still use sprintf() because there's some formatted strings in the output
            return sprintf($compiledOutput, '');
        }

        // Clear previous output
        $newLineCount = substr_count($this->outputFormat, PHP_EOL);

        return sprintf("\033[2K\033[0G\033[{$newLineCount}A\033[2K$compiledOutput", '', '');
    }

    /**
     * Gets the estimated time remaining
     *
     * @return string The estimated time remaining
     */
    private function getEstimatedTimeRemaining(): string
    {
        if ($this->progress === 0) {
            // We cannot estimate the time remaining if no progress has been made
            return 'Estimating...';
        }

        if ($this->progress === $this->maxSteps) {
            return 'Complete';
        }

        $elapsedTime = time() - $this->startTime->getTimestamp();
        $secondsRemaining = round($elapsedTime * $this->maxSteps / $this->progress - $elapsedTime);
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
                    || count($timeFormats) === $index - 1
                ) {
                    if (count($timeFormat) === 2) {
                        return $timeFormat[1];
                    }

                    return floor($secondsRemaining / $timeFormat[2]) . ' ' . $timeFormat[1];
                }
            }
        }

        return 'Estimating...';
    }
}
