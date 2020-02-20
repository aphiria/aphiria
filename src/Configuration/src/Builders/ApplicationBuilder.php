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
use Aphiria\Configuration\Middleware\MiddlewareBinding;
use Aphiria\Console\App as ConsoleApp;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandBus;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Serialization\Encoding\IEncoder;
use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use RuntimeException;

/**
 * Defines an application builder
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
final class ApplicationBuilder implements IApplicationBuilder
{
    /** @var IContainer The DI container to resolve dependencies with */
    private IContainer $container;
    /** @var IComponentBuilder[] The mapping of component builder names to builders */
    private array $componentBuilderFactories = [];
    /** @var Closure[] The mapping of component builder names to the enqueued list of component builder calls */
    private array $componentBuilderCalls = [];
    /** @var Closure[] The mapping of magic method names to callbacks to execute */
    private array $componentMagicMethodsToCallbacks = [];

    /**
     * @param IContainer $container The DI container to resolve dependencies with
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Attempts to configure a component builder
     *
     * @param string $methodName The name of the method that was invoked
     * @param array $args The list of arguments that was passed in
     * @return self For chaining
     * @throws BadMethodCallException Thrown if the magic method does not exist
     */
    public function __call(string $methodName, array $args)
    {
        if (!isset($this->componentMagicMethodsToCallbacks[$methodName])) {
            throw new BadMethodCallException("No magic method with named $methodName is registered");
        }

        $callback = $this->componentMagicMethodsToCallbacks[$methodName];
        $callback(...$args);

        return $this;
    }


    /**
     * @inheritdoc
     */
    public function buildApiApplication(): IRequestHandler
    {
        $this->buildComponents();

        /** @var IRequestHandler $router */
        $router = null;
        $this->container->for(ApiApp::class, static function (IContainer $container) use (&$router) {
            if (!$container->tryResolve(IRequestHandler::class, $router)) {
                throw new RuntimeException('No ' . IRequestHandler::class . ' router bound to container');
            }
        });
        try {
            $apiApp = new ApiApp($router, $this->container->resolve(MiddlewareCollection::class));
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the API application', 0, $ex);
        }

        $this->container->bindInstance(IRequestHandler::class, $apiApp);

        return $apiApp;
    }

    /**
     * @inheritdoc
     */
    public function buildConsoleApplication(): ICommandBus
    {
        $this->buildComponents();
        try {
            $consoleApp = new ConsoleApp($this->container->resolve(CommandRegistry::class));
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build the console application', 0, $ex);
        }

        $this->container->bindInstance(ICommandBus::class, $consoleApp);

        return $consoleApp;
    }

    /**
     * @inheritdoc
     */
    public function configureComponentBuilder(string $class, Closure $callback): void
    {
        if (!isset($this->componentBuilderFactories[$class])) {
            throw new InvalidArgumentException("No component builder of type $class is registered");
        }

        if (!isset($this->componentBuilderCalls[$class])) {
            $this->componentBuilderCalls[$class] = [];
        }

        $this->componentBuilderCalls[$class][] = $callback;
    }

    /**
     * @inheritdoc
     */
    public function withComponentBuilder(string $class, Closure $factory, array $magicMethods = []): IApplicationBuilder
    {
        $this->componentBuilderFactories[$class] = $factory;

        foreach ($magicMethods as $magicMethodName => $callback) {
            if (isset($this->componentMagicMethodsToCallbacks[$magicMethodName])) {
                throw new InvalidArgumentException("Magic method $magicMethodName is already registered");
            }

            $this->componentMagicMethodsToCallbacks[$magicMethodName] = $callback;
        }

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
