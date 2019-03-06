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
 * Defines the interface for application builders to implement
 */
interface IApplicationBuilder
{
    /**
     * Adds bootstrappers to the application
     *
     * @param Closure $delegate The delegate that will add bootstrappers (must accept a bootstrapper registry)
     * @return IApplicationBuilder For chaining
     */
    public function withBootstrappers(Closure $delegate): self;

    /**
     * Adds console commands to the application
     *
     * @param Closure $delegate The delegate that will add console commands (must accept a command registry)
     * @return IApplicationBuilder For chaining
     */
    public function withCommands(Closure $delegate): self;

    /**
     * Adds an entire module to the application
     *
     * @param IModuleBuilder $moduleBuilder The module builder to include
     * @return IApplicationBuilder For chaining
     */
    public function withModule(IModuleBuilder $moduleBuilder): self;

    /**
     * Adds routes to the application
     *
     * @param Closure $delegate The delegate that will add routes (must accept a route builder registry)
     * @return IApplicationBuilder For chaining
     */
    public function withRoutes(Closure $delegate): self;
}
