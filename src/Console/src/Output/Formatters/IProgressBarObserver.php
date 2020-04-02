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
 * Defines the interface for progress bar observers to implement
 */
interface IProgressBarObserver
{
    /**
     * Handles an update to a progress bar
     *
     * @param int|null $prevProgress The previous progress if there was one, otherwise null
     * @param int $currProgress The current progress
     * @param int $maxSteps The max number of steps that can be taken
     */
    public function onProgressChanged(?int $prevProgress, int $currProgress, int $maxSteps): void;
}
