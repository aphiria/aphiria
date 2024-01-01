<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application;

/**
 * Defines the interface implemented by classes that configure whole modules of code
 */
interface IModule
{
    /**
     * Configures the module's components with the input app builder
     *
     * @param IApplicationBuilder $appBuilder The app builder to use
     */
    public function configure(IApplicationBuilder $appBuilder): void;
}
