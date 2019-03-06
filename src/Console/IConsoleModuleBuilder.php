<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

namespace Aphiria\Configuration\Console;

/**
 * Defines the interface implemented by classes that build whole modules of code
 */
interface IConsoleModuleBuilder
{
    /**
     * Builds the entire module into an application
     *
     * @param IConsoleApplicationBuilder $appBuilder The app builder to use
     */
    public function build(IConsoleApplicationBuilder $appBuilder): void;
}
