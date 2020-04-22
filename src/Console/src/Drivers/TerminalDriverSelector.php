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

/**
 * Defines what selects the driver to use
 */
class TerminalDriverSelector
{
    /**
     * Selects the appropriate terminal driver for this OS
     *
     * @return ITerminalDriver The terminal driver to use
     */
    public function select(): ITerminalDriver
    {
        return DIRECTORY_SEPARATOR === '\\' ? new WindowsTerminalDriver() : new UnixTerminalDriver();
    }
}
