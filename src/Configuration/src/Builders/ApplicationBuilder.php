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

use Aphiria\Api\App as ApiApp;
use Aphiria\Configuration\Framework\Console\Builders\CommandBuilder;
use Aphiria\Configuration\Framework\DependencyInjection\Builders\BootstrapperBuilder;
use Aphiria\Configuration\Framework\Middleware\Builders\MiddlewareBuilder;
use Aphiria\Configuration\Middleware\MiddlewareBinding;
use Aphiria\Console\App as ConsoleApp;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Closure;
use RuntimeException;

/**
 * Defines an application builder
 */
final class ApplicationBuilder implements IApplicationBuilder
{
    /** @var IContainer The DI container to resolve dependencies with */
    private IContainer $container;
    /** @var IComponentBuilder[] The mapping of component builder names to builders */
    private array $componentBuilderFactories = [];
    /** @var Closure[] The mapping of component builder names to the enqueued list of component builder calls */
    private array $componentBuilderCalls = [];
    /** @var Closure|null The callback that will resolve the router request handler */
    private ?Closure $routerCallback = null;
    /** @var MiddlewareCollection The list of global middleware */
    private MiddlewareCollection $middlewareCollection;
    /** @var CommandRegistry The console commands */
    private CommandRegistry $commands;

    /**
     * @param IContainer $container The DI container to resolve dependencies with
     * @param IBootstrapperDispatcher $bootstrapperDispatcher The bootstrapper dispatcher
     */
    public function __construct(IContainer $container, IBootstrapperDispatcher $bootstrapperDispatcher)
    {
        $this->container = $container;

        // TODO: I might need to remove the bootstrapper and middleware components if they're going to stick in AphiriaComponentBuilder

        // It's important for the bootstrapper component builder is registered first so that all the dependencies are bound
        $this->withComponentBuilder(BootstrapperBuilder::class, fn () => new BootstrapperBuilder($bootstrapperDispatcher));

        // We'll need the middleware collection bound bound early on so that we can add to it from modules and before bootstrappers are run
        $this->container->bindInstance(MiddlewareCollection::class, $this->middlewareCollection = new MiddlewareCollection());
        $this->container->bindInstance(MiddlewareCollection::class, $middlewareCollection = new MiddlewareCollection());
        $this->withComponentBuilder(MiddlewareBuilder::class, fn () => new MiddlewareBuilder($this->middlewareCollection, $this->container));

        // We'll need the commands bound bound early on so that we can add to them from modules and before bootstrappers are run
        $this->container->bindInstance(CommandRegistry::class, $this->commands = new CommandRegistry());
        $this->withComponentBuilder(CommandBuilder::class, fn () => $this->container->resolve(CommandBuilder::class));
    }

    /**
     * @inheritdoc
     */
    public function buildApiApplication(): IRequestHandler
    {
        $this->buildComponents();

        if ($this->routerCallback === null) {
            throw new RuntimeException('Router callback not set');
        }

        if (!($router = ($this->routerCallback)()) instanceof IRequestHandler) {
            throw new RuntimeException('Router must implement ' . IRequestHandler::class);
        }

        // TODO: If I move configuring middleware to AphiriaComponentBuilder, I need to use the DI container to resolve the middleware collection
        $apiApp = new ApiApp($router, $this->middlewareCollection);
        $this->container->bindInstance(IRequestHandler::class, $apiApp);

        return $apiApp;
    }

    /**
     * @inheritdoc
     */
    public function buildConsoleApplication(): ICommandBus
    {
        $this->buildComponents();
        $consoleApp = new ConsoleApp($this->commands);
        $this->container->bindInstance(ICommandBus::class, $consoleApp);

        return $consoleApp;
    }

    /**
     * @inheritdoc
     */
    public function configureComponentBuilder(string $class, Closure $callback): void
    {
        if (!isset($this->componentBuilderFactories[$class])) {
            // TODO: What type of exception should I throw?
            throw new \Exception('TODO');
        }

        if (!isset($this->componentBuilderCalls[$class])) {
            $this->componentBuilderCalls[$class] = [];
        }

        $this->componentBuilderCalls[$class][] = $callback;
    }

    /**
     * @inheritdoc
     */
    public function withBootstrapper(Bootstrapper $bootstrapper): IApplicationBuilder
    {
        $this->configureComponentBuilder(
            BootstrapperBuilder::class,
            fn (BootstrapperBuilder $bootstrapperBuilder) => $bootstrapperBuilder->withBootstrapper($bootstrapper)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withComponentBuilder(string $class, Closure $factory): IApplicationBuilder
    {
        $this->componentBuilderFactories[$class] = $factory;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withConsoleCommands(Closure $callback): IApplicationBuilder
    {
        $this->configureComponentBuilder(
            CommandBuilder::class,
            fn (CommandBuilder $commandBuilder) => $commandBuilder->withCommands($callback)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withGlobalMiddleware(MiddlewareBinding $middlewareBinding): IApplicationBuilder
    {
        $this->configureComponentBuilder(
            MiddlewareBuilder::class,
            fn (MiddlewareBuilder $middlewareBuilder) => $middlewareBuilder->withMiddlewareBinding($middlewareBinding)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withManyBootstrappers(array $bootstrappers): IApplicationBuilder
    {
        $this->configureComponentBuilder(
            BootstrapperBuilder::class,
            fn (BootstrapperBuilder $bootstrapperBuilder) => $bootstrapperBuilder->withManyBootstrappers($bootstrappers)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withManyGlobalMiddleware(array $middlewareBindings): IApplicationBuilder
    {
        $this->configureComponentBuilder(
            MiddlewareBuilder::class,
            fn (MiddlewareBuilder $middlewareBuilder) => $middlewareBuilder->withManyMiddlewareBindings($middlewareBindings)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withModuleBuilder(IModuleBuilder $moduleBuilder): IApplicationBuilder
    {
        $moduleBuilder->build($this);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withRouter(Closure $routerCallback): IApplicationBuilder
    {
        $this->routerCallback = $routerCallback;

        return $this;
    }

    /**
     * Builds all the registered component builders
     */
    private function buildComponents(): void
    {
        foreach ($this->componentBuilderFactories as $componentBuilderName => $componentBuilderFactory) {
            /** @var IComponentBuilder $componentBuilder */
            $componentBuilder = $componentBuilderFactory();

            // Calls to component builders should happen before they're built
            if (isset($this->componentBuilderCalls[$componentBuilderName])) {
                foreach ($this->componentBuilderCalls[$componentBuilderName] as $componentBuilderCallback) {
                    $componentBuilderCallback($componentBuilder);
                }
            }

            $componentBuilder->build($this);
        }
    }
}
