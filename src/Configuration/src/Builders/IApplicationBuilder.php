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

use Aphiria\Console\Commands\ICommandBus;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
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
     * Enqueues a call to a component builder
     *
     * @param string $class The name of the component builder to call
     * @param Closure $callback The callback that will take an instance of the class param
     */
    public function enqueueComponentBuilderCall(string $class, Closure $callback): void;

    /**
     * Adds a bootstrapper to the application
     *
     * @param Bootstrapper $bootstrapper The bootstrapper to add
     * @return IApplicationBuilder For chaining
     */
    public function withBootstrapper(Bootstrapper $bootstrapper): IApplicationBuilder;

    /**
     * Adds bootstrappers to the application
     *
     * @param Bootstrapper[] $bootstrappers The bootstrappers to add
     * @return IApplicationBuilder For chaining
     */
    public function withBootstrappers(array $bootstrappers): self;

    /**
     * Adds a component builder to the application
     *
     * @param string $class The name of the component builder class
     * @param Closure $factory The factory that will create the component builder
     * @return IApplicationBuilder For chaining
     */
    public function withComponentBuilder(string $class, Closure $factory): self;

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
     * Adds an entire module builder to the application
     *
     * @param IModuleBuilder $moduleBuilder The module builder to include
     * @return IApplicationBuilder For chaining
     */
    public function withModuleBuilder(IModuleBuilder $moduleBuilder): self;

    /**
     * Adds the inner-most request handler that will act as the router
     *
     * @param Closure $routerCallback The callback that takes in no parameters and returns an instance of a request handler
     * @return IApplicationBuilder For chaining
     */
    public function withRouter(Closure $routerCallback): self;
}
