<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Application\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\ResolutionException;
use Aphiria\Framework\Console\Components\CommandComponent;
use Aphiria\Framework\DependencyInjection\Components\BootstrapperComponent;
use Aphiria\Framework\Exceptions\Components\ExceptionHandlerComponent;
use Aphiria\Framework\Middleware\Components\MiddlewareComponent;
use Aphiria\Framework\Routing\Components\RouterComponent;
use Aphiria\Framework\Serialization\Components\SerializerComponent;
use Aphiria\Framework\Validation\Components\ValidationComponent;
use Aphiria\Middleware\MiddlewareBinding;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Serialization\Encoding\IEncoder;
use Closure;

/**
 * Defines a Aphiria component builder that gives a fluent syntax for enabling/configuring Aphiria components
 */
final class AphiriaComponentBuilder
{
    /** @var IContainer The DI container */
    private IContainer $container;

    /**
     * @param IContainer $container The DI container
     */
    public function __construct(IContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Adds bootstrappers to the bootstrapper component builder
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param Bootstrapper|Bootstrapper[] $bootstrappers The bootstrapper or list of bootstrappers to add
     * @return self For chaining
     */
    public function withBootstrappers(IApplicationBuilder $appBuilder, $bootstrappers): self
    {
        $this->withBootstrapperComponent($appBuilder)
            ->withBootstrappers($bootstrappers);

        return $this;
    }

    /**
     * Enables console command annotations
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return self For chaining
     */
    public function withCommandAnnotations(IApplicationBuilder $appBuilder): self
    {
        $this->withCommandComponent($appBuilder)
            ->withAnnotations();

        return $this;
    }

    /**
     * Adds console commands to the command component builder
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param Closure $callback The callback that takes in an instance of CommandRegistry to register commands to
     * @return self For chaining
     */
    public function withCommands(IApplicationBuilder $appBuilder, Closure $callback): self
    {
        $this->withCommandComponent($appBuilder)
            ->withCommands($callback);

        return $this;
    }

    /**
     * Adds an encoder to the encoder component builder
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param string $class The class whose encoder we're registering
     * @param IEncoder $encoder The encoder to register
     * @return self For chaining
     */
    public function withEncoder(IApplicationBuilder $appBuilder, string $class, IEncoder $encoder): self
    {
        $this->withSerializerComponent($appBuilder)
            ->withEncoder($class, $encoder);

        return $this;
    }

    /**
     * Adds the exception handler middleware to the beginning of the middleware collection
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return self For chaining
     */
    public function withExceptionHandlerMiddleware(IApplicationBuilder $appBuilder): self
    {
        $this->withExceptionHandlerComponent($appBuilder)
            ->withExceptionHandlerMiddleware();

        return $this;
    }

    /**
     * Adds an exception response factory to the exception handler component builder
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param string $exceptionType The type of exception whose response factory we're registering
     * @param Closure $responseFactory The factory that takes in an instance of the exception, ?IHttpRequestMessage, and INegotiatedResponseFactory and creates a response
     * @return self For chaining
     */
    public function withExceptionResponseFactory(IApplicationBuilder $appBuilder, string $exceptionType, Closure $responseFactory): self
    {
        $this->withExceptionHandlerComponent($appBuilder)
            ->withResponseFactory($exceptionType, $responseFactory);

        return $this;
    }

    /**
     * Adds global middleware bindings to the middleware component builder
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param MiddlewareBinding|MiddlewareBinding[] $middlewareBindings The middleware binding or list of bindings to add
     * @return self For chaining
     */
    public function withGlobalMiddleware(IApplicationBuilder $appBuilder, $middlewareBindings): self
    {
        $this->withMiddlewareComponent($appBuilder)
            ->withGlobalMiddleware($middlewareBindings);

        return $this;
    }

    /**
     * Adds a log level factory to the exception handler component builder
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param string $exceptionType The exception type whose factory we're registering
     * @param Closure $logLevelFactory The factory that takes in an instance of the exception and returns the PSR-3 log level
     * @return self For chaining
     */
    public function withLogLevelFactory(IApplicationBuilder $appBuilder, string $exceptionType, Closure $logLevelFactory): self
    {
        $this->withExceptionHandlerComponent($appBuilder)
            ->withLogLevelFactory($exceptionType, $logLevelFactory);

        return $this;
    }

    /**
     * Adds object constraints to the object constraints component builder
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param Closure $callback The callback that takes in an instance of ObjectConstraintsRegistry to register object constraints to
     * @return self For chaining
     */
    public function withObjectConstraints(IApplicationBuilder $appBuilder, Closure $callback): self
    {
        $this->withValidationComponent($appBuilder)
            ->withObjectConstraints($callback);

        return $this;
    }

    /**
     * Enables routing annotations
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return self For chaining
     */
    public function withRouteAnnotations(IApplicationBuilder $appBuilder): self
    {
        $this->withRouterComponent($appBuilder)
            ->withAnnotations();

        return $this;
    }

    /**
     * Adds routes to the router component builder
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @param Closure $callback The callback that takes in an instance of RouteBuilderRegistry to register route builders to
     * @return self For chaining
     */
    public function withRoutes(IApplicationBuilder $appBuilder, Closure $callback): self
    {
        $this->withRouterComponent($appBuilder)
            ->withRoutes($callback);

        return $this;
    }

    /**
     * Enables Aphiria validation annotations
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return self For chaining
     */
    public function withValidatorAnnotations(IApplicationBuilder $appBuilder): self
    {
        $this->withValidationComponent($appBuilder)
            ->withAnnotations();

        return $this;
    }

    /**
     * Registers the bootstrapper component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return BootstrapperComponent The bootstrapper component
     */
    private function withBootstrapperComponent(IApplicationBuilder $appBuilder): BootstrapperComponent
    {
        if (!$appBuilder->hasComponent(BootstrapperComponent::class)) {
            $appBuilder->withComponent(
                new BootstrapperComponent($this->container->resolve(IBootstrapperDispatcher::class)),
                0
            );
        }

        return $appBuilder->getComponent(BootstrapperComponent::class);
    }

    /**
     * Registers the command component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return CommandComponent The command component
     */
    private function withCommandComponent(IApplicationBuilder $appBuilder): CommandComponent
    {
        if (!$appBuilder->hasComponent(CommandComponent::class)) {
            // Bind the command registry here so that it can be used in the component
            if (!$this->container->hasBinding(CommandRegistry::class)) {
                $this->container->bindInstance(CommandRegistry::class, new CommandRegistry());
            }

            $appBuilder->withComponent(new CommandComponent($this->container));
        }

        return $appBuilder->getComponent(CommandComponent::class);
    }

    /**
     * Registers the Aphiria exception handler component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return ExceptionHandlerComponent The exception handler component
     */
    private function withExceptionHandlerComponent(IApplicationBuilder $appBuilder): ExceptionHandlerComponent
    {
        if (!$appBuilder->hasComponent(ExceptionHandlerComponent::class)) {
            $appBuilder->withComponent(new ExceptionHandlerComponent($this->container, $appBuilder));
        }

        return $appBuilder->getComponent(ExceptionHandlerComponent::class);
    }

    /**
     * Registers the middleware component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return MiddlewareComponent The middleware component
     * @throws ResolutionException Thrown if the middleware collection could not be resolved
     */
    private function withMiddlewareComponent(IApplicationBuilder $appBuilder): MiddlewareComponent
    {
        if (!$appBuilder->hasComponent(MiddlewareComponent::class)) {
            // Bind the middleware collection here so that it can be used in the component
            $this->container->hasBinding(MiddlewareCollection::class)
                ? $middlewareCollection= $this->container->resolve(MiddlewareCollection::class)
                : $this->container->bindInstance(MiddlewareCollection::class, $middlewareCollection = new MiddlewareCollection());
            $appBuilder->withComponent(new MiddlewareComponent($this->container));
        }

        return $appBuilder->getComponent(MiddlewareComponent::class);
    }

    /**
     * Registers the Aphiria routing component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return RouterComponent The router component
     */
    private function withRouterComponent(IApplicationBuilder $appBuilder): RouterComponent
    {
        if (!$appBuilder->hasComponent(RouterComponent::class)) {
            $appBuilder->withComponent(new RouterComponent($this->container));
        }

        return $appBuilder->getComponent(RouterComponent::class);
    }

    /**
     * Registers Aphiria serializer component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return SerializerComponent The serializer component
     */
    private function withSerializerComponent(IApplicationBuilder $appBuilder): SerializerComponent
    {
        if (!$appBuilder->hasComponent(SerializerComponent::class)) {
            $appBuilder->withComponent(new SerializerComponent($this->container));
        }

        return $appBuilder->getComponent(SerializerComponent::class);
    }

    /**
     * Registers the Aphiria validation component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return ValidationComponent The validation component
     */
    private function withValidationComponent(IApplicationBuilder $appBuilder): ValidationComponent
    {
        if (!$appBuilder->hasComponent(ValidationComponent::class)) {
            $appBuilder->withComponent(new ValidationComponent($this->container));
        }

        return $appBuilder->getComponent(ValidationComponent::class);
    }
}
