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

use Aphiria\Api\Router;
use Aphiria\Configuration\Middleware\MiddlewareBinding;
use Aphiria\Console\Commands\AggregateCommandRegistrant;
use Aphiria\Console\Commands\Annotations\AnnotationCommandRegistrant;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Exceptions\ExceptionResponseFactoryRegistry;
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\Middleware\ExceptionHandler;
use Aphiria\Routing\AggregateRouteRegistrant;
use Aphiria\Routing\Annotations\AnnotationRouteRegistrant;
use Aphiria\Routing\Builders\RouteBuilderRouteRegistrant;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Validation\Builders\ObjectConstraintsBuilderRegistrant;
use Aphiria\Validation\Constraints\AggregateObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
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
    /** @var bool Whether or not the validation component was registered */
    private bool $validationComponentRegistered = false;

    /**
     * @param IContainer $container The DI container to resolve dependencies with
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Registers Aphiria console annotations
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withConsoleAnnotations(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->registerComponentBuilder('consoleAnnotations', function (array $callbacks) {
            /** @var AnnotationCommandRegistrant $annotationCommandRegistrant */
            $annotationCommandRegistrant = null;

            if (!$this->container->tryResolve(AnnotationCommandRegistrant::class, $annotationCommandRegistrant)) {
                throw new RuntimeException('No ' . AnnotationCommandRegistrant::class . ' is bound to the container');
            }

            /** @var AggregateCommandRegistrant $commandRegistrant */
            $this->container->hasBinding(AggregateCommandRegistrant::class)
                ? $commandRegistrant = $this->container->resolve(AggregateCommandRegistrant::class)
                : $this->container->bindInstance(AggregateCommandRegistrant::class,
                $commandRegistrant = new AggregateCommandRegistrant());

            $commandRegistrant->addCommandRegistrant($annotationCommandRegistrant);
        });

        return $this;
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
            /** @var EncoderRegistry $encoders */
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
            /** @var GlobalExceptionHandler $globalExceptionHandler */
            $this->container->hasBinding(GlobalExceptionHandler::class)
                ? $globalExceptionHandler = $this->container->resolve(GlobalExceptionHandler::class)
                : $this->container->bindInstance(GlobalExceptionHandler::class,
                $globalExceptionHandler = new GlobalExceptionHandler());

            $globalExceptionHandler->registerWithPhp();
            $appBuilder->withGlobalMiddleware(fn() => [new MiddlewareBinding(ExceptionHandler::class)]);
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
            /** @var ExceptionLogLevelFactoryRegistry $exceptionLogLevelFactories */
            $this->container->hasBinding(ExceptionLogLevelFactoryRegistry::class)
                ? $exceptionLogLevelFactories = $this->container->resolve(ExceptionLogLevelFactoryRegistry::class)
                : $this->container->bindInstance(ExceptionLogLevelFactoryRegistry::class,
                $exceptionLogLevelFactories = new ExceptionLogLevelFactoryRegistry());

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
            /** @var ExceptionResponseFactoryRegistry $exceptionResponseFactories */
            $this->container->hasBinding(ExceptionResponseFactoryRegistry::class)
                ? $exceptionResponseFactories = $this->container->resolve(ExceptionResponseFactoryRegistry::class)
                : $this->container->bindInstance(ExceptionResponseFactoryRegistry::class,
                $exceptionResponseFactories = new ExceptionResponseFactoryRegistry());

            foreach ($callbacks as $callback) {
                $callback($exceptionResponseFactories);
            }
        });

        return $this;
    }

    /**
     * Registers Aphiria routing annotations (requires the routing component to be registered)
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withRoutingAnnotations(IApplicationBuilder $appBuilder): self
    {
        if (!$this->routingComponentRegistered) {
            $this->withRoutingComponent($appBuilder);
        }

        $appBuilder->registerComponentBuilder('routeAnnotations', function (array $callbacks) {
            /** @var AnnotationRouteRegistrant $annotationRouteRegistrant */
            $annotationRouteRegistrant = null;

            if (!$this->container->tryResolve(AnnotationRouteRegistrant::class, $annotationRouteRegistrant)) {
                throw new RuntimeException('No ' . AnnotationRouteRegistrant::class . ' is bound to the container');
            }

            /** @var AggregateRouteRegistrant $aggregateRouteRegistrant */
            $this->container->hasBinding(AggregateRouteRegistrant::class)
                ? $aggregateRouteRegistrant = $this->container->resolve(AggregateRouteRegistrant::class)
                : $this->container->bindInstance(AggregateRouteRegistrant::class,
                $aggregateRouteRegistrant = new AggregateRouteRegistrant());

            $aggregateRouteRegistrant->addRouteRegistrant($annotationRouteRegistrant);
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
        if ($this->routingComponentRegistered) {
            // Don't double-register this
            return $this;
        }

        $this->routingComponentRegistered = true;
        // Set up the router request handler
        $appBuilder->withRouter(fn() => $this->container->resolve(Router::class));
        // Register the routing component
        $appBuilder->registerComponentBuilder('routes', function (array $callbacks) {
            /** @var AggregateRouteRegistrant $aggregateRouteRegistrant */
            $this->container->hasBinding(AggregateRouteRegistrant::class)
                ? $aggregateRouteRegistrant = $this->container->resolve(AggregateRouteRegistrant::class)
                : $this->container->bindInstance(AggregateRouteRegistrant::class,
                $aggregateRouteRegistrant = new AggregateRouteRegistrant());

            $aggregateRouteRegistrant->addRouteRegistrant(new RouteBuilderRouteRegistrant($callbacks));
        });

        return $this;
    }

    /**
     * Registers Aphiria validation annotations
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withValidationAnnotations(IApplicationBuilder $appBuilder): self
    {
        if (!$this->validationComponentRegistered) {
            $this->withValidationComponent($appBuilder);
        }

        $appBuilder->registerComponentBuilder('validationAnnotations', function (array $callbacks) {
            /** @var AnnotationObjectConstraintsRegistrant $annotationConstraintRegistrant */
            $annotationConstraintRegistrant = null;

            if (!$this->container->tryResolve(AnnotationObjectConstraintsRegistrant::class,
                $annotationConstraintRegistrant)) {
                throw new RuntimeException('No ' . AnnotationObjectConstraintsRegistrant::class . ' is bound to the container');
            }

            /** @var AggregateObjectConstraintsRegistrant $aggregateConstraintRegistrant */
            $this->container->hasBinding(AggregateObjectConstraintsRegistrant::class)
                ? $aggregateConstraintRegistrant = $this->container->resolve(AggregateObjectConstraintsRegistrant::class)
                : $this->container->bindInstance(AggregateObjectConstraintsRegistrant::class,
                $aggregateConstraintRegistrant = new AggregateObjectConstraintsRegistrant());

            $aggregateConstraintRegistrant->addConstraintRegistrant($annotationConstraintRegistrant);
        });

        return $this;
    }

    /**
     * Registers Aphiria validators
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withValidationComponent(IApplicationBuilder $appBuilder): self
    {
        if ($this->validationComponentRegistered) {
            // Don't double-register this component
            return $this;
        }

        $appBuilder->registerComponentBuilder('validators', function (array $callbacks) {
            /** @var AggregateObjectConstraintsRegistrant $aggregateConstraintRegistrant */
            $this->container->hasBinding(AggregateObjectConstraintsRegistrant::class)
                ? $aggregateConstraintRegistrant = $this->container->resolve(AggregateObjectConstraintsRegistrant::class)
                : $this->container->bindInstance(AggregateObjectConstraintsRegistrant::class,
                $aggregateConstraintRegistrant = new AggregateObjectConstraintsRegistrant());

            $aggregateConstraintRegistrant->addConstraintRegistrant(new ObjectConstraintsBuilderRegistrant($callbacks));

            /** @var ObjectConstraintsRegistry $objectConstraints */
            $this->container->hasBinding(ObjectConstraintsRegistry::class)
                ? $objectConstraints = $this->container->resolve(ObjectConstraintsRegistry::class)
                : $this->container->bindInstance(ObjectConstraintsRegistry::class,
                $objectConstraints = new ObjectConstraintsRegistry());

            $aggregateConstraintRegistrant->registerConstraints($objectConstraints);
        });

        return $this;
    }
}
