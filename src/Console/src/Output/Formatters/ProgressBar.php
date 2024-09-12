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

use Exception;
use InvalidArgumentException;

/**
 * Defines a progress bar
 */
final class ProgressBar
{
    /** @var bool Whether or not the progress bar is complete */
    public bool $isComplete {
        get => $this->maxSteps === $this->_progress;
    }
    /** @var int The current progress */
    public int $progress {
        set {
            // Bound the progress between 0 and the max steps
            $prevProgress = $this->_progress;
            $this->_progress = \max(0, \min($this->maxSteps, $value));

            // Don't call the observers if no progress was actually made
            if ($prevProgress !== $this->_progress) {
                $this->observer->onProgressChanged($prevProgress, $this->_progress, $this->maxSteps, $this->options);
            }
        }
    }
    /** @var int|null The current progress, or null if no progress has been made yet */
    private ?int $_progress = null;

    /**
     * @param int $maxSteps The max number of steps
     * @param IProgressBarObserver $observer The observer that will draw the progress bar
     * @param ProgressBarFormatterOptions $options The options to use
     * @throws InvalidArgumentException Thrown if the max steps are invalid
     */
    public function __construct(
        private readonly int $maxSteps,
        private readonly IProgressBarObserver $observer,
        private readonly ProgressBarFormatterOptions $options = new ProgressBarFormatterOptions()
    ) {
        if ($this->maxSteps <= 0) {
            throw new InvalidArgumentException('Max steps must be greater than 0');
        }
    }

    /**
     * Advances the progress one step
     *
     * @param int $step The amount to step by
     * @throws Exception Thrown if there was an error writing the output
     */
    public function advance(int $step = 1): void
    {
        $this->progress = ($this->_progress ?? 0) + $step;
    }

    /**
     * Finishes advancing the progress bar
     *
     * @throws Exception Thrown if there was an error writing the output
     */
    public function complete(): void
    {
        $this->progress = $this->maxSteps;
    }
}
