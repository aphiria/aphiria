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

use Aphiria\Configuration\Middleware\MiddlewareBinding;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Serialization\Encoding\IEncoder;
use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines the interface for applications builders to implement
 *
 * @method IApplicationBuilder withBootstrappers(Bootstrapper|Bootstrapper[] $bootstrapper)
 * @method IApplicationBuilder withCommands(Closure $callback)
 * @method IApplicationBuilder withEncoder(string $class, IEncoder $encoder)
 * @method IApplicationBuilder withExceptionResponseFactory(string $exceptionType, Closure $responseFactory)
 * @method IApplicationBuilder withGlobalMiddleware(MiddlewareBinding|MiddlewareBinding[] $middlewareBindings)
 * @method IApplicationBuilder withLogLevelFactory(string $exceptionType, Closure $logLevelFactory)
 * @method IApplicationBuilder withObjectConstraints(Closure $callback)
 * @method IApplicationBuilder withRoutes(Closure $callback)
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
     * Configures a component builder by registering a callback that will manipulate it
     *
     * @param string $class The name of the component builder to call
     * @param Closure $callback The callback that will take an instance of the class param
     * @throws InvalidArgumentException Thrown if the component builder was not registered yet
     */
    public function configureComponentBuilder(string $class, Closure $callback): void;

    /**
     * Adds a component builder to the application
     *
     * @param string $class The name of the component builder class
     * @param Closure $factory The factory that will create the component builder
     * @param array $magicMethods The mapping of magic method names to callbacks
     * @return IApplicationBuilder For chaining
     * @throws InvalidArgumentException Thrown if the magic method was already registered
     */
    public function withComponentBuilder(string $class, Closure $factory, array $magicMethods = []): self;

    /**
     * Adds an entire module builder to the application
     *
     * @param IModuleBuilder $moduleBuilder The module builder to include
     * @return IApplicationBuilder For chaining
     */
    public function withModuleBuilder(IModuleBuilder $moduleBuilder): self;
}
