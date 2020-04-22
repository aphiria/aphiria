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
class CliDriverSelector
{
    /**
     * Selects the appropriate CLI driver for this OS
     *
     * @return ICliDriver The CLI driver to use
     */
    public function select(): ICliDriver
    {
        return DIRECTORY_SEPARATOR === '\\' ? new WindowsDriver() : new UnixLikeDriver();
    }
}
