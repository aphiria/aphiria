<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application;

use RuntimeException;

/**
 * Defines the interface for applications to implement
 */
interface IApplication
{
    /**
     * Runs the application
     *
     * @return int The exit code of the application
     * @throws RuntimeException Thrown if there was an unhandled exception
     */
    public function run(): int;
}
