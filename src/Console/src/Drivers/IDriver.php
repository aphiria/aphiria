<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Drivers;

use Aphiria\Console\Output\IOutput;

/**
 * Defines the interface for CLI drivers to implement
 */
interface IDriver
{
    /** @var The height of the CLI */
    public int $cliHeight { get; }
    /** @var The width of the CLI */
    public int $cliWidth { get; }

    /**
     * Gets the hidden input value
     *
     * @param IOutput $output The current output
     * @return string|null The value of the input, or null if none was entered
     * @throws HiddenInputNotSupportedException Thrown if hidden input is not supported
     */
    public function readHiddenInput(IOutput $output): ?string;
}
