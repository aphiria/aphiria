<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Drivers;

/**
 * Defines what selects the driver to use
 */
class DriverSelector
{
    /**
     * Selects the appropriate CLI driver for this OS
     *
     * @return IDriver The CLI driver to use
     */
    public function select(): IDriver
    {
        return DIRECTORY_SEPARATOR === '\\' ? new WindowsDriver() : new UnixLikeDriver();
    }
}
