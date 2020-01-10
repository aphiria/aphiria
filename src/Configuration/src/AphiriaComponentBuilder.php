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
use Aphiria\Console\Commands\Annotations\AnnotationCommandRegistrant;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Exceptions\ExceptionResponseFactoryRegistry;
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\Middleware\ExceptionHandler;
use Aphiria\Routing\Annotations\AnnotationRouteRegistrant;
use Aphiria\Routing\Builders\RouteBuilderRouteRegistrant;
use Aphiria\Routing\RouteRegistrantCollection;
use Aphiria\Serialization\Encoding\EncoderRegistry;
use Aphiria\Validation\Builders\ObjectConstraintsBuilderRegistrant;
use Aphiria\Validation\Constraints\Annotations\AnnotationObjectConstraintsRegistrant;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistrantCollection;
use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use RuntimeException;

/**
 * Defines the component builder for Aphiria components
 */
final class AphiriaComponentBuilder
{
    /** @var IContainer The DI container to resolve dependencies with */
    private IContainer $container;

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
        if ($appBuilder->hasComponentBuilder('consoleAnnotations')) {
            // Don't double-register this
            return $this;
        }

        $appBuilder->registerComponentBuilder('consoleAnnotations', function (array $callbacks) {
            /** @var AnnotationCommandRegistrant $annotationCommandRegistrant */
            $annotationCommandRegistrant = null;

            if (!$this->container->tryResolve(AnnotationCommandRegistrant::class, $annotationCommandRegistrant)) {
                throw new RuntimeException('No ' . AnnotationCommandRegistrant::class . ' is bound to the container');
            }

            /** @var CommandRegistrantCollection $commandRegistrants */
            $this->container->hasBinding(CommandRegistrantCollection::class)
                ? $commandRegistrants = $this->container->resolve(CommandRegistrantCollection::class)
                : $this->container->bindInstance(CommandRegistrantCollection::class,
                $commandRegistrants = new CommandRegistrantCollection());

            $commandRegistrants->add($annotationCommandRegistrant);
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
        if ($appBuilder->hasComponentBuilder('encoders')) {
            // Don't double-register this
            return $this;
        }

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
        if ($appBuilder->hasComponentBuilder('exceptionHandlers')) {
            // Don't double-register this
            return $this;
        }

        $appBuilder->registerComponentBuilder('exceptionHandlers', function (array $callbacks) use ($appBuilder) {
            /** @var GlobalExceptionHandler $globalExceptionHandler */
            $this->container->hasBinding(GlobalExceptionHandler::class)
                ? $globalExceptionHandler = $this->container->resolve(GlobalExceptionHandler::class)
                : $this->container->bindInstance(
                    GlobalExceptionHandler::class,
                    $globalExceptionHandler = new GlobalExceptionHandler());

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
        if ($appBuilder->hasComponentBuilder('exceptionLogLevelFactories')) {
            // Don't double-register this
            return $this;
        }

        $appBuilder->registerComponentBuilder('exceptionLogLevelFactories', function (array $callbacks) {
            /** @var ExceptionLogLevelFactoryRegistry $exceptionLogLevelFactories */
            $this->container->hasBinding(ExceptionLogLevelFactoryRegistry::class)
                ? $exceptionLogLevelFactories = $this->container->resolve(ExceptionLogLevelFactoryRegistry::class)
                : $this->container->bindInstance(
                    ExceptionLogLevelFactoryRegistry::class,
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
        if ($appBuilder->hasComponentBuilder('exceptionResponseFactories')) {
            // Don't double-register this
            return $this;
        }

        $appBuilder->registerComponentBuilder('exceptionResponseFactories', function (array $callbacks) {
            /** @var ExceptionResponseFactoryRegistry $exceptionResponseFactories */
            $this->container->hasBinding(ExceptionResponseFactoryRegistry::class)
                ? $exceptionResponseFactories = $this->container->resolve(ExceptionResponseFactoryRegistry::class)
                : $this->container->bindInstance(
                    ExceptionResponseFactoryRegistry::class,
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
        if ($appBuilder->hasComponentBuilder('routeAnnotations')) {
            // Don't double-register this
            return $this;
        }

        if (!$appBuilder->hasComponentBuilder('routes')) {
            $this->withRoutingComponent($appBuilder);
        }

        $appBuilder->registerComponentBuilder('routeAnnotations', function (array $callbacks) {
            /** @var AnnotationRouteRegistrant $annotationRouteRegistrant */
            $annotationRouteRegistrant = null;

            if (!$this->container->tryResolve(AnnotationRouteRegistrant::class, $annotationRouteRegistrant)) {
                throw new RuntimeException('No ' . AnnotationRouteRegistrant::class . ' is bound to the container');
            }

            /** @var RouteRegistrantCollection $routeRegistrants */
            $this->container->hasBinding(RouteRegistrantCollection::class)
                ? $routeRegistrants = $this->container->resolve(RouteRegistrantCollection::class)
                : $this->container->bindInstance(
                    RouteRegistrantCollection::class,
                    $routeRegistrants = new RouteRegistrantCollection());

            $routeRegistrants->add($annotationRouteRegistrant);
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
        if ($appBuilder->hasComponentBuilder('routes')) {
            // Don't double-register this
            return $this;
        }

        // Set up the router request handler
        $appBuilder->withRouter(fn () => $this->container->resolve(Router::class));
        // Register the routing component
        $appBuilder->registerComponentBuilder('routes', function (array $callbacks) {
            /** @var RouteRegistrantCollection $routeRegistrants */
            $this->container->hasBinding(RouteRegistrantCollection::class)
                ? $routeRegistrants = $this->container->resolve(RouteRegistrantCollection::class)
                : $this->container->bindInstance(
                    RouteRegistrantCollection::class,
                    $routeRegistrants = new RouteRegistrantCollection());

            $routeRegistrants->add(new RouteBuilderRouteRegistrant($callbacks));
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
        if ($appBuilder->hasComponentBuilder('validationAnnotations')) {
            // Don't double-register this
            return $this;
        }

        if (!$appBuilder->hasComponentBuilder('validators')) {
            $this->withValidationComponent($appBuilder);
        }

        $appBuilder->registerComponentBuilder('validationAnnotations', function (array $callbacks) {
            /** @var AnnotationObjectConstraintsRegistrant $annotationConstraintsRegistrant */
            $annotationConstraintsRegistrant = null;

            if (
                !$this->container->tryResolve(
                    AnnotationObjectConstraintsRegistrant::class,
                    $annotationConstraintsRegistrant
                )
            ) {
                throw new RuntimeException('No ' . AnnotationObjectConstraintsRegistrant::class . ' is bound to the container');
            }

            /** @var ObjectConstraintsRegistrantCollection $constraintsRegistrants */
            $this->container->hasBinding(ObjectConstraintsRegistrantCollection::class)
                ? $constraintsRegistrants = $this->container->resolve(ObjectConstraintsRegistrantCollection::class)
                : $this->container->bindInstance(
                    ObjectConstraintsRegistrantCollection::class,
                    $constraintsRegistrants = new ObjectConstraintsRegistrantCollection());

            $constraintsRegistrants->add($annotationConstraintsRegistrant);

            // We're now ready to register all the object constraints from annotations + ones registered manually
            $this->registerObjectConstraints($constraintsRegistrants);
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
        if ($appBuilder->hasComponentBuilder('validators')) {
            // Don't double-register this
            return $this;
        }

        $appBuilder->registerComponentBuilder('validators', function (array $callbacks) use ($appBuilder) {
            /** @var ObjectConstraintsRegistrantCollection $constraintsRegistrants */
            $this->container->hasBinding(ObjectConstraintsRegistrantCollection::class)
                ? $constraintsRegistrants = $this->container->resolve(ObjectConstraintsRegistrantCollection::class)
                : $this->container->bindInstance(
                    ObjectConstraintsRegistrantCollection::class,
                    $constraintsRegistrants = new ObjectConstraintsRegistrantCollection());

            $constraintsRegistrants->add(new ObjectConstraintsBuilderRegistrant($callbacks));

            /**
             * If we're not using annotations, then we're all set to actually register the constraints now.
             * Otherwise, we'll register them in the annotation component
             */
            if (!$appBuilder->hasComponentBuilder('validationAnnotations')) {
                $this->registerObjectConstraints($constraintsRegistrants);
            }
        });

        return $this;
    }

    /**
     * Registers the object constraints
     * This is separated out because if we are using the validation component but not annotations, we want to run this
     * immediately after the validation component is built.  Otherwise, if we are using annotations, we want it to run
     * after the annotation component is built.  This way, constraints found in annotations also get registered.
     *
     * @param ObjectConstraintsRegistrantCollection $constraintsRegistrants The constraints registrants
     * @throws ResolutionException Thrown if the cached constraints registrant couldn't be resolved
     */
    private function registerObjectConstraints(ObjectConstraintsRegistrantCollection $constraintsRegistrants): void
    {
        /** @var ObjectConstraintsRegistry $objectConstraints */
        $this->container->hasBinding(ObjectConstraintsRegistry::class)
            ? $objectConstraints = $this->container->resolve(ObjectConstraintsRegistry::class)
            : $this->container->bindInstance(
            ObjectConstraintsRegistry::class,
            $objectConstraints = new ObjectConstraintsRegistry());

        $constraintsRegistrants->registerConstraints($objectConstraints);
    }
}
