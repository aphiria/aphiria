<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application;

/**
 * Defines the interface for application bootstrappers to implement
 */
interface IBootstrapper
{
    /**
     * Bootstraps part of the application so that it can run (happens at the very beginning of application startup)
     */
    public function bootstrap(): void;
}
