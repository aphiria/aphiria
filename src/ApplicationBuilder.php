<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration;

use Aphiria\Api\App;
use Aphiria\Api\ContainerDependencyResolver;
use Aphiria\Api\DependencyResolutionException;
use Aphiria\Api\IDependencyResolver;
use Aphiria\Middleware\MiddlewarePipelineFactory;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Closure;
use InvalidArgumentException;
use Opulence\Ioc\Bootstrappers\Bootstrapper;
use Opulence\Ioc\Bootstrappers\IBootstrapperDispatcher;
use Opulence\Ioc\IContainer;
use Opulence\Ioc\ResolutionException;
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
     * @throws InvalidArgumentException Thrown if no component exists with the input name
     */
    public function __call(string $methodName, array $arguments): IApplicationBuilder
    {
        // Remove "with"
        $componentName = substr($methodName, 4);

        return $this->withComponent($componentName, ...$arguments);
    }

    /**
     * @inheritdoc
     */
    public function build(): IRequestHandler
    {
        try {
            /** @var Bootstrapper[] $bootstrappers */
            $bootstrappers = [];

            foreach ($this->bootstrapperCallbacks as $bootstrapperCallback) {
                foreach ((array)$bootstrapperCallback() as $bootstrapper) {
                    $bootstrappers[] = $bootstrapper;
                }
            }

            $this->bootstrapperDispatcher->dispatch($bootstrappers);

            foreach ($this->components as $normalizedComponentName => $componentConfig) {
                /** @var Closure $factory */
                $factory = $componentConfig['factory'];
                $factory($componentConfig['callbacks']);
            }

            return $this->createApp();
        } catch (ResolutionException $ex) {
            throw new RuntimeException('Failed to build app', 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function registerComponentFactory(string $componentName, Closure $factory): IApplicationBuilder
    {
        $this->components[self::normalizeComponentName($componentName)] = ['factory' => $factory, 'callbacks' => []];

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
            throw new InvalidArgumentException("$componentName does not have a factory registered");
        }

        $this->components[$normalizedComponentName]['callbacks'][] = $callback;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withMiddleware(Closure $middlewareCallback): IApplicationBuilder
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
     * Creates the app request handler
     *
     * @return IRequestHandler The application request handler
     * @throws RuntimeException Thrown if the kernel callback was not registered
     * @throws ResolutionException Thrown if there was an error creating any dependencies
     */
    protected function createApp(): IRequestHandler
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

        $app = new App($dependencyResolver, $router, $middlewarePipelineFactory);

        try {
            foreach ($this->middlewareCallbacks as $middlewareCallback) {
                // Todo: How do I add attributes?
                foreach ((array)$middlewareCallback() as $middlewareClass) {
                    $app->addMiddleware($middlewareClass);
                }
            }
        } catch (DependencyResolutionException $ex) {
            throw new ResolutionException($ex->getInterface(), $ex->getTargetClass(), 'Failed to resolve middleware', 0, $ex);
        }

        return $app;
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
