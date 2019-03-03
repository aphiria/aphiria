<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

namespace Aphiria\Configuration;

use Closure;

/**
 * Defines the interface for console application builders to implement
 */
interface IConsoleApplicationBuilder extends IApplicationBuilder
{
    /**
     * Builds the console application
     */
    public function build(): void;

    /**
     * Adds console commands to the application
     *
     * @param Closure $delegate The delegate that will add console commands (must accept a command registry)
     * @return IConsoleApplicationBuilder For chaining
     */
    public function withCommands(Closure $delegate): self;
}
