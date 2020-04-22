<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Drivers;

use Aphiria\Console\Output\IOutput;

/**
 * Defines the interface for terminal drivers to implement
 */
interface ITerminalDriver
{
    /**
     * Gets the height of the terminal
     *
     * @return int The height of the terminal
     */
    public function getTerminalHeight(): int;

    /**
     * Gets the width of the terminal
     *
     * @return int The width of the terminal
     */
    public function getTerminalWidth(): int;

    /**
     * Gets the hidden input value
     *
     * @param IOutput $output The current output
     * @return string|null The value of the input, or null if none was entered
     * @throws HiddenInputNotSupportedException Thrown if hidden input is not supported
     */
    public function readHiddenInput(IOutput $output): ?string;
}
