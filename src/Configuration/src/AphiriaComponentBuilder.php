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

use Aphiria\Api\Router;
use Aphiria\Configuration\Middleware\MiddlewareBinding;
use Aphiria\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Exceptions\ExceptionResponseFactoryRegistry;
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\Middleware\ExceptionHandler;
use Aphiria\RouteAnnotations\IRouteAnnotationRegistrant;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\LazyRouteFactory;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Opulence\Ioc\IContainer;
use RuntimeException;

/**
 * Defines the component builder for Aphiria components
 */
final class AphiriaComponentBuilder
{
    /** @var IContainer The DI container to resolve dependencies with */
    private IContainer $container;
    /** @var bool Whether or not the routing component was registered */
    private bool $routingComponentRegistered = false;

    /**
     * @param IContainer $container The DI container to resolve dependencies with
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Registers Aphiria encoders
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withEncoderComponent(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->registerComponentBuilder('encoders', function (array $callbacks) {
            $this->container->hasBinding(EncoderRegistry::class)
                ? $encoders = $this->container->resolve(EncoderRegistry::class)
                : $this->container->bindInstance(EncoderRegistry::class, $encoders = new EncoderRegistry());

            foreach ($callbacks as $callback) {
                $callback($encoders);
            }
        });

        return $this;
    }

    /**
     * Registers the Aphiria exception handlers
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withExceptionHandlers(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->registerComponentBuilder('exceptionHandlers', function (array $callbacks) use ($appBuilder) {
            $this->container->hasBinding(GlobalExceptionHandler::class)
                ? $globalExceptionHandler = $this->container->resolve(GlobalExceptionHandler::class)
                : $this->container->bindInstance(GlobalExceptionHandler::class, $globalExceptionHandler = new GlobalExceptionHandler());
            $globalExceptionHandler->registerWithPhp();
            $appBuilder->withGlobalMiddleware(fn () => [new MiddlewareBinding(ExceptionHandler::class)]);
        });

        return $this;
    }

    /**
     * Registers the Aphiria exception log level factory component
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withExceptionLogLevelFactories(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->registerComponentBuilder('exceptionLogLevelFactories', function (array $callbacks) {
            $this->container->hasBinding(ExceptionLogLevelFactoryRegistry::class)
                ? $exceptionLogLevelFactories = $this->container->resolve(ExceptionLogLevelFactoryRegistry::class)
                : $this->container->bindInstance(ExceptionLogLevelFactoryRegistry::class, $exceptionLogLevelFactories = new ExceptionLogLevelFactoryRegistry());

            foreach ($callbacks as $callback) {
                $callback($exceptionLogLevelFactories);
            }
        });

        return $this;
    }

    /**
     * Registers the Aphiria exception response factory component
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withExceptionResponseFactories(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->registerComponentBuilder('exceptionResponseFactories', function (array $callbacks) {
            $this->container->hasBinding(ExceptionResponseFactoryRegistry::class)
                ? $exceptionResponseFactories = $this->container->resolve(ExceptionResponseFactoryRegistry::class)
                : $this->container->bindInstance(ExceptionResponseFactoryRegistry::class, $exceptionResponseFactories = new ExceptionResponseFactoryRegistry());

            foreach ($callbacks as $callback) {
                $callback($exceptionResponseFactories);
            }
        });

        return $this;
    }

    /**
     * Registers Aphiria route annotations (requires the routing component to be registered)
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     * @throws RuntimeException Thrown if the routing component was not registered already
     */
    public function withRouteAnnotations(IApplicationBuilder $appBuilder): self
    {
        if (!$this->routingComponentRegistered) {
            throw new RuntimeException('Routing component must be enabled via withRoutingComponent() to use route annotations');
        }

        $appBuilder->registerComponentBuilder('routeAnnotations', function (array $callbacks) {
            $this->container->hasBinding(LazyRouteFactory::class)
                ? $routeFactory = $this->container->resolve(LazyRouteFactory::class)
                : $this->container->bindInstance(LazyRouteFactory::class, $routeFactory = new LazyRouteFactory());
            /** @var IRouteAnnotationRegistrant $routeAnnotationRegistrant */
            $routeAnnotationRegistrant = $this->container->resolve(IRouteAnnotationRegistrant::class);
            $routeFactory->addFactory(function () use ($routeAnnotationRegistrant) {
                $routes = new RouteBuilderRegistry();
                $routeAnnotationRegistrant->registerRoutes($routes);

                return $routes->buildAll();
            });
        });

        return $this;
    }

    /**
     * Registers the Aphiria router
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withRoutingComponent(IApplicationBuilder $appBuilder): self
    {
        $this->routingComponentRegistered = true;
        // Set up the router request handler
        $appBuilder->withRouter(fn () => $this->container->resolve(Router::class));
        // Register the routing component
        $appBuilder->registerComponentBuilder('routes', function (array $callbacks) {
            $this->container->hasBinding(LazyRouteFactory::class)
                ? $routeFactory = $this->container->resolve(LazyRouteFactory::class)
                : $this->container->bindInstance(LazyRouteFactory::class, $routeFactory = new LazyRouteFactory());

            $routeFactory->addFactory(function () use ($callbacks) {
                $routeBuilders = new RouteBuilderRegistry();

                foreach ($callbacks as $callback) {
                    $callback($routeBuilders);
                }

                return $routeBuilders->buildAll();
            });
        });

        return $this;
    }
}
