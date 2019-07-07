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

use Exception;
use InvalidArgumentException;

/**
 * Defines a progress bar
 */
final class ProgressBar
{
    /** @var int The maximum number of steps that can be taken */
    private int $maxSteps;
    /** @var int|null The current progress, or null if no progress has been made yet */
    private ?int $progress = null;
    /** @var IProgressBarObserver The observer that will draw the progress bar */
    private IProgressBarObserver $observer;

    /**
     * @param int $maxSteps The max number of steps
     * @param IProgressBarObserver $observer The observer that will draw the progress bar
     * @throws InvalidArgumentException Thrown if the max steps are invalid
     */
    public function __construct(int $maxSteps, IProgressBarObserver $observer)
    {
        if ($maxSteps <= 0) {
            throw new InvalidArgumentException('Max steps must be greater than 0');
        }

        $this->maxSteps = $maxSteps;
        $this->observer = $observer;
    }

    /**
     * Advances the progress one step
     *
     * @param int $step The amount to step by
     * @throws Exception Thrown if there was an error writing the output
     */
    public function advance(int $step = 1): void
    {
        $this->setProgress(($this->progress ?? 0) + $step);
    }

    /**
     * Finishes advancing the progress bar
     *
     * @throws Exception Thrown if there was an error writing the output
     */
	public function complete(): void
    {
        $this->setProgress($this->maxSteps);
    }

    /**
     * Gets whether or not the progress bar is complete
     *
     * @return bool True if the progress bar is complete, otherwise false
     */
	public function isComplete(): bool
    {
        return $this->maxSteps === $this->progress;
    }

    /**
     * Sets the current progress
     *
     * @param int $progress The current progress
     * @throws Exception Thrown if there was an error formatting the output
     */
	public function setProgress(int $progress): void
    {
        // Bound the progress between 0 and the max steps
        $prevProgress = $this->progress;
        $this->progress = max(0, min($this->maxSteps, $progress ?? 0));

        // Don't call the observers if no progress was actually made
        if ($prevProgress !== $this->progress) {
            $this->observer->onProgressChanged($prevProgress, $this->progress, $this->maxSteps);
        }
    }
}
