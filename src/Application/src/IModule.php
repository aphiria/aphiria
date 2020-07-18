<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application;

use Aphiria\Application\Builders\IApplicationBuilder;

/**
 * Defines the interface implemented by classes that build whole modules of code
 */
interface IModule
{
    /**
     * Builds the module's components with the input app builder
     *
     * @param IApplicationBuilder $appBuilder The app builder to use
     */
    public function build(IApplicationBuilder $appBuilder): void;
}
