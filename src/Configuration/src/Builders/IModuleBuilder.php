<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Builders;

/**
 * Defines the interface implemented by classes that build whole modules of code
 */
interface IModuleBuilder
{
    /**
     * Builds the entire module into an application
     *
     * @param IApplicationBuilder $appBuilder The app builder to use
     */
    public function build(IApplicationBuilder $appBuilder): void;
}
