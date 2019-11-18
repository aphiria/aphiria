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

use Aphiria\Api\App as ApiApp;
use Aphiria\Api\ContainerDependencyResolver;
use Aphiria\Api\DependencyResolutionException;
use Aphiria\Api\IDependencyResolver;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Configuration\Middleware\MiddlewareBinding;
use Aphiria\Console\App as ConsoleApp;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\Console\Commands\AggregateCommandRegistrant;
use Aphiria\Console\Commands\ICommandRegistrant;
use Aphiria\Middleware\MiddlewarePipelineFactory;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines an application builder
 */
final class ApplicationBuilder implements IApplicationBuilder
{
    /** @var IContainer The DI container to resolve dependencies with */
    private IContainer $container;
    /** @var IBootstrapperDispatcher The bootstrapper dispatcher */
    private IBootstrapperDispatcher $bootstrapperDispatcher;
    /** @var Closure[] The mapping of builder names to callbacks */
    private array $components = [];
    /** @var Closure[] The list of bootstrapper callbacks */
    private array $bootstrapperCallbacks = [];
    /** @var Closure[] The list of console command callbacks */
    private array $consoleCommandCallbacks = [];
    /** @var Closure|null The callback that will resolve the router request handler */
    private ?Closure $routerCallback = null;
    /** @var Closure[] The list of middleware callbacks */
    private array $middlewareCallbacks = [];

    /**
     * @param IContainer $container The DI container to resolve dependencies with
     * @param IBootstrapperDispatcher $bootstrapperDispatcher The bootstrapper dispatcher
     */
    public function __construct(IContainer $container, IBootstrapperDispatcher $bootstrapperDispatcher)
    {
        $this->container = $container;
        $this->bootstrapperDispatcher = $bootstrapperDispatcher;
    }

    /**
     * Allows us to add components using a more fluent syntax
     *
     * @param string $methodName The name of the method that was called
     * @param array $arguments The arguments that were passed in
     * @return IApplicationBuilder For chaining
     * @throws BadMethodCallException Thrown if the method name does not start with "with"
     * @throws InvalidArgumentException Thrown if no component exists with the input name
     */
    public function __call(string $methodName, array $arguments): IApplicationBuilder
    {
        // Method name must be "with{component}", and component must be at least one character
        if (\strlen($methodName) < 5 || strpos($methodName, 'with') !== 0) {
            throw new BadMethodCallException("Method $methodName is not supported");
        }

        // Remove "with"
        $componentName = substr($methodName, 4);

        return $this->withComponent($componentName, ...$arguments);
    }

    /**
     * @inheritdoc
     */
    public function buildApiApplication(): IRequestHandler
    {
        try {
            $this->dispatchBootstrappers();
            $this->buildComponents();
            $apiApp = $this->createRequestHandler();
            $this->container->bindInstance(IRequestHandler::class, $apiApp);

            return $apiApp;
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build API app', 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function buildConsoleApplication(): ICommandBus
    {
        try {
            $this->dispatchBootstrappers();
            $this->container->hasBinding(AggregateCommandRegistrant::class)
                ? $commandRegistrant = $this->container->resolve(AggregateCommandRegistrant::class)
                : $this->container->bindInstance(AggregateCommandRegistrant::class, $commandRegistrant = new AggregateCommandRegistrant());

            $this->registerConsoleCommands($commandRegistrant);
            $this->buildComponents();
            /** @var CommandRegistry $commands */
            $this->container->hasBinding(CommandRegistry::class)
                ? $commands = $this->container->resolve(CommandRegistry::class)
                : $this->container->bindInstance(CommandRegistry::class, $commands = new CommandRegistry());

            $commandRegistrant->registerCommands($commands);
            $consoleApp = new ConsoleApp($commands);
            $this->container->bindInstance(ICommandBus::class, $consoleApp);

            return $consoleApp;
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build console app', 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerComponentBuilder(string $componentName, Closure $builder): IApplicationBuilder
    {
        $this->components[self::normalizeComponentName($componentName)] = ['builder' => $builder, 'callbacks' => []];

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withBootstrappers(Closure $callback): IApplicationBuilder
    {
        $this->bootstrapperCallbacks[] = $callback;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withComponent(string $componentName, Closure $callback): IApplicationBuilder
    {
        $normalizedComponentName = self::normalizeComponentName($componentName);

        if (!isset($this->components[$normalizedComponentName])) {
            throw new InvalidArgumentException("$componentName does not have a builder registered");
        }

        $this->components[$normalizedComponentName]['callbacks'][] = $callback;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withConsoleCommands(Closure $callback): IApplicationBuilder
    {
        $this->consoleCommandCallbacks[] = $callback;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withGlobalMiddleware(Closure $middlewareCallback): IApplicationBuilder
    {
        $this->middlewareCallbacks[] = $middlewareCallback;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withModule(IModuleBuilder $moduleBuilder): IApplicationBuilder
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
     * Builds all the registered components
     */
    private function buildComponents(): void
    {
        foreach ($this->components as $normalizedComponentName => $componentConfig) {
            /** @var Closure $builder */
            $builder = $componentConfig['builder'];
            $builder($componentConfig['callbacks']);
        }
    }

    /**
     * Builds the console commands that were registered
     *
     * @param AggregateCommandRegistrant $commandRegistrant The command regisrant to register commands to
     */
    private function registerConsoleCommands(AggregateCommandRegistrant $commandRegistrant): void
    {
        $commandRegistrant->addCommandRegistrant(new class ($this->consoleCommandCallbacks) implements ICommandRegistrant {
            private array $consoleCommandCallbacks;

            /**
             * @param Closure[] $consoleCommandCallbacks The callbacks to execute
             */
            public function __construct(array $consoleCommandCallbacks)
            {
                $this->consoleCommandCallbacks = $consoleCommandCallbacks;
            }

            /**
             * @inheritdoc
             */
            public function registerCommands(CommandRegistry $commands): void
            {
                foreach ($this->consoleCommandCallbacks as $callback) {
                    $callback($commands);
                }
            }
        });
    }

    /**
     * Creates the app request handler
     *
     * @return IRequestHandler The application request handler
     * @throws RuntimeException Thrown if the kernel callback was not registered
     * @throws ResolutionException Thrown if there was an error creating any dependencies
     */
    private function createRequestHandler(): IRequestHandler
    {
        if ($this->routerCallback === null) {
            throw new RuntimeException('Router callback not set');
        }

        if (!($router = ($this->routerCallback)()) instanceof IRequestHandler) {
            throw new RuntimeException('Router must implement ' . IRequestHandler::class);
        }

        $this->container->hasBinding(IDependencyResolver::class)
            ? $dependencyResolver = $this->container->resolve(IDependencyResolver::class)
            : $this->container->bindInstance(
                IDependencyResolver::class,
                $dependencyResolver = new ContainerDependencyResolver($this->container)
            );
        $this->container->hasBinding(MiddlewarePipelineFactory::class)
            ? $middlewarePipelineFactory = $this->container->resolve(MiddlewarePipelineFactory::class)
            : $this->container->bindInstance(
                MiddlewarePipelineFactory::class,
                $middlewarePipelineFactory = new MiddlewarePipelineFactory()
            );

        $app = new ApiApp($dependencyResolver, $router, $middlewarePipelineFactory);

        try {
            foreach ($this->middlewareCallbacks as $middlewareCallback) {
                /** @var MiddlewareBinding $middlewareBinding */
                foreach ((array)$middlewareCallback() as $middlewareBinding) {
                    if (!$middlewareBinding instanceof MiddlewareBinding) {
                        throw new RuntimeException('Middleware bindings must be an instance of ' . MiddlewareBinding::class);
                    }

                    $app->addMiddleware($middlewareBinding->className, $middlewareBinding->attributes);
                }
            }
        } catch (DependencyResolutionException $ex) {
            throw new ResolutionException($ex->getInterface(), $ex->getTargetClass(), 'Failed to resolve middleware', 0, $ex);
        }

        return $app;
    }

    /**
     * Dispatches all the registered bootstrappers
     */
    private function dispatchBootstrappers(): void
    {
        /** @var Bootstrapper[] $bootstrappers */
        $bootstrappers = [];

        foreach ($this->bootstrapperCallbacks as $bootstrapperCallback) {
            $bootstrappers = [...$bootstrappers, ...(array)$bootstrapperCallback()];
        }

        $this->bootstrapperDispatcher->dispatch($bootstrappers);
    }

    /**
     * Normalizes a component name so that it can be called with a magic method
     *
     * @param string $componentName The name of the component to normalize
     * @return string The normalized component name
     */
    private static function normalizeComponentName(string $componentName): string
    {
        return \lcfirst(\preg_replace('/[^a-z0-9_]/i', '', $componentName));
    }
}
