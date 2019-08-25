<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration;

use Aphiria\Console\Commands\ICommandBus;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Closure;
use RuntimeException;

/**
 * Defines the interface for applications builders to implement
 */
interface IApplicationBuilder
{
    /**
     * Builds an API application
     *
     * @return IRequestHandler The top-level request handler
     * @throws RuntimeException Thrown if there was an error building the application
     */
    public function buildApiApplication(): IRequestHandler;

    /**
     * Builds a console application
     *
     * @return ICommandBus The top-level command bus
     * @throws RuntimeException Thrown if there was an error building the application
     */
    public function buildConsoleApplication(): ICommandBus;

    /**
     * Registers a component to the app
     *
     * @param string $componentName The name of the component the callback belongs to
     * @param Closure $builder The factory that will take in an IContainer and list of callbacks registered for this component ands builds it
     * @return IApplicationBuilder For chaining
     */
    public function registerComponentBuilder(string $componentName, Closure $builder): self;

    /**
     * Adds bootstrappers to the application
     *
     * @param Closure $callback The callback that will return instantiated bootstrappers
     * @return IApplicationBuilder For chaining
     */
    public function withBootstrappers(Closure $callback): self;

    /**
     * Adds a component to the app
     *
     * @param string $componentName The name of the component the callback belongs to
     * @param Closure $callback The callback that must take in an IContainer and the list of callbacks registered for this component
     * @return IApplicationBuilder For chaining
     */
    public function withComponent(string $componentName, Closure $callback): self;

    /**
     * Adds console commands to the application
     *
     * @param Closure $callback The callback that takes in a CommandRegistry ands registers commands to it
     * @return IApplicationBuilder For chaining
     */
    public function withConsoleCommands(Closure $callback): self;

    /**
     * Adds global middleware to the app
     *
     * @param Closure $middlewareCallback The callback that will return the list of middleware bindings to use
     * @return IApplicationBuilder For chaining
     */
    public function withGlobalMiddleware(Closure $middlewareCallback): self;

    /**
     * Adds an entire module to the application
     *
     * @param IModuleBuilder $moduleBuilder The module builder to include
     * @return IApplicationBuilder For chaining
     */
    public function withModule(IModuleBuilder $moduleBuilder): self;

    /**
     * Adds the inner-most request handler that will act as the router
     *
     * @param Closure $routerCallback The callback that takes in no parameters and returns an instance of a request handler
     * @return IApplicationBuilder For chaining
     */
    public function withRouter(Closure $routerCallback): self;
}
