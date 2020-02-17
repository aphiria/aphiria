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
use Aphiria\Configuration\Middleware\MiddlewareBinding;
use Aphiria\Console\App as ConsoleApp;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Middleware\MiddlewarePipelineFactory;
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
    /** @var Closure[] The list of middleware callbacks */
    private array $middlewareCallbacks = [];

    /**
     * @param IContainer $container The DI container to resolve dependencies with
     * @param IBootstrapperDispatcher $bootstrapperDispatcher The bootstrapper dispatcher
     */
    public function __construct(IContainer $container, IBootstrapperDispatcher $bootstrapperDispatcher)
    {
        $this->container = $container;
        // It's important for the bootstrapper component builder is registered first so that all the dependencies are bound
        $this->withComponentBuilder(BootstrapperBuilder::class, fn () => new BootstrapperBuilder($bootstrapperDispatcher));
    }

    /**
     * @inheritdoc
     */
    public function buildApiApplication(): IRequestHandler
    {
        try {
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
            // TODO: Do I need to move this to the constructor so that the component builder is available to modules prior to this method being called?
            $this->withComponentBuilder(
                CommandBuilder::class,
                fn () => $this->container->resolve(CommandBuilder::class)
            );
            $this->buildComponents();
            // TODO: Do I need to explicitly resolve CommandRegistry and make sure the same instance is passed into CommandBuilder and ConsoleApp?
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
    public function enqueueComponentBuilderCall(string $class, Closure $callback): void
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
        $this->enqueueComponentBuilderCall(
            BootstrapperBuilder::class,
            fn (BootstrapperBuilder $bootstrapperBuilder) => $bootstrapperBuilder->withBootstrapper($bootstrapper)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withBootstrappers(array $bootstrappers): IApplicationBuilder
    {
        $this->enqueueComponentBuilderCall(
            BootstrapperBuilder::class,
            fn (BootstrapperBuilder $bootstrapperBuilder) => $bootstrapperBuilder->withBootstrappers($bootstrappers)
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
        $this->enqueueComponentBuilderCall(
            CommandBuilder::class,
            fn (CommandBuilder $commandBuilder) => $commandBuilder->withCommands($callback)
        );

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
        foreach ($this->componentBuilderFactories as $componentBuilderName => $componentBuilder) {
            $componentBuilder->build($this);

            if (isset($this->componentBuilderCalls[$componentBuilderName])) {
                foreach ($this->componentBuilderCalls[$componentBuilderName] as $componentBuilderCall) {
                    $componentBuilderCall($componentBuilder);
                }
            }
        }
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

        $this->container->hasBinding(MiddlewarePipelineFactory::class)
            ? $middlewarePipelineFactory = $this->container->resolve(MiddlewarePipelineFactory::class)
            : $this->container->bindInstance(
            MiddlewarePipelineFactory::class,
            $middlewarePipelineFactory = new MiddlewarePipelineFactory()
        );

        $app = new ApiApp($this->container, $router, $middlewarePipelineFactory);

        // TODO: Not sure how I can refactor middleware to be a component.  That requires resolving ApiApp and passing it into the builder (which needs to be instantiated in this class' constructor).  However, at that point I haven't bound a router or middleware pipeline factory.
        // TODO: One way around this would be to keep track of middleware in a registry which is injected into ApiApp and the middleware component builder.
        foreach ($this->middlewareCallbacks as $middlewareCallback) {
            /** @var MiddlewareBinding $middlewareBinding */
            foreach ((array)$middlewareCallback() as $middlewareBinding) {
                if (!$middlewareBinding instanceof MiddlewareBinding) {
                    throw new RuntimeException('Middleware bindings must be an instance of ' . MiddlewareBinding::class);
                }

                $app->addMiddleware($middlewareBinding->className, $middlewareBinding->attributes);
            }
        }

        return $app;
    }
}
