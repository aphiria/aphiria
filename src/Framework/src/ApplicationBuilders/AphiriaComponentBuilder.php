<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\ApplicationBuilders;

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Console\Builders\CommandBuilder;
use Aphiria\Framework\Console\Builders\CommandBuilderProxy;
use Aphiria\Framework\DependencyInjection\Builders\BootstrapperBuilder;
use Aphiria\Framework\DependencyInjection\Builders\BootstrapperBuilderProxy;
use Aphiria\Framework\Exceptions\Builders\ExceptionHandlerBuilder;
use Aphiria\Framework\Exceptions\Builders\ExceptionHandlerBuilderProxy;
use Aphiria\Framework\Middleware\Builders\MiddlewareBuilder;
use Aphiria\Framework\Middleware\Builders\MiddlewareBuilderProxy;
use Aphiria\Framework\Routing\Builders\RouterBuilder;
use Aphiria\Framework\Routing\Builders\RouterBuilderProxy;
use Aphiria\Framework\Serialization\Builders\SerializerBuilder;
use Aphiria\Framework\Serialization\Builders\SerializerBuilderProxy;
use Aphiria\Framework\Validation\Builders\ValidatorBuilder;
use Aphiria\Framework\Validation\Builders\ValidatorBuilderProxy;
use Aphiria\Middleware\MiddlewareBinding;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Serialization\Encoding\IEncoder;
use Closure;

/**
 * Defines a Aphiria component builder that gives a fluent syntax for enabling/configuring Aphiria components
 */
class AphiriaComponentBuilder
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
            ->getComponentBuilder(BootstrapperBuilder::class)
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
        $this->withConsoleComponent($appBuilder)
            ->getComponentBuilder(CommandBuilder::class)
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
        $this->withConsoleComponent($appBuilder)
            ->getComponentBuilder(CommandBuilder::class)
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
            ->getComponentBuilder(SerializerBuilder::class)
            ->withEncoder($class, $encoder);

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
            ->getComponentBuilder(ExceptionHandlerBuilder::class)
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
            ->getComponentBuilder(MiddlewareBuilder::class)
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
            ->getComponentBuilder(ExceptionHandlerBuilder::class)
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
        $this->withValidatorComponent($appBuilder)
            ->getComponentBuilder(ValidatorBuilder::class)
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
            ->getComponentBuilder(RouterBuilder::class)
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
            ->getComponentBuilder(RouterBuilder::class)
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
        $this->withValidatorComponent($appBuilder)
            ->getComponentBuilder(ValidatorBuilder::class)
            ->withAnnotations();

        return $this;
    }

    /**
     * Registers the bootstrapper component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return IApplicationBuilder For chaining
     */
    private function withBootstrapperComponent(IApplicationBuilder $appBuilder): IApplicationBuilder
    {
        if ($appBuilder->hasComponentBuilder(BootstrapperBuilder::class)) {
            return $appBuilder;
        }

        return $appBuilder->withComponentBuilder(new BootstrapperBuilderProxy(fn () => $this->container->resolve(BootstrapperBuilder::class)), 0);
    }

    /**
     * Registers the console component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return IApplicationBuilder For chaining
     */
    private function withConsoleComponent(IApplicationBuilder $appBuilder): IApplicationBuilder
    {
        if ($appBuilder->hasComponentBuilder(CommandBuilder::class)) {
            return $appBuilder;
        }

        // Bind the command registry here so that it can be injected into the component builder
        $this->container->bindInstance(CommandRegistry::class, new CommandRegistry());

        return $appBuilder->withComponentBuilder(new CommandBuilderProxy(fn () => $this->container->resolve(CommandBuilder::class)));
    }

    /**
     * Registers the Aphiria exception handler component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return IApplicationBuilder For chaining
     */
    private function withExceptionHandlerComponent(IApplicationBuilder $appBuilder): IApplicationBuilder
    {
        if ($appBuilder->hasComponentBuilder(ExceptionHandlerBuilder::class)) {
            return $appBuilder;
        }

        return $appBuilder->withComponentBuilder(new ExceptionHandlerBuilderProxy(fn () => $this->container->resolve(ExceptionHandlerBuilder::class)));
    }

    /**
     * Registers the middleware component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return IApplicationBuilder For chaining
     */
    private function withMiddlewareComponent(IApplicationBuilder $appBuilder): IApplicationBuilder
    {
        if ($appBuilder->hasComponentBuilder(MiddlewareBuilder::class)) {
            return $appBuilder;
        }

        // Bind the middleware collection here so that it can be injected into the component builder
        $this->container->hasBinding(MiddlewareCollection::class)
            ? $middlewareCollection= $this->container->resolve(MiddlewareCollection::class)
            : $this->container->bindInstance(MiddlewareCollection::class, $middlewareCollection = new MiddlewareCollection());

        return $appBuilder->withComponentBuilder(new MiddlewareBuilderProxy(fn () => new MiddlewareBuilder($middlewareCollection, $this->container)));
    }

    /**
     * Registers the Aphiria routing component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return IApplicationBuilder For chaining
     */
    private function withRouterComponent(IApplicationBuilder $appBuilder): IApplicationBuilder
    {
        if ($appBuilder->hasComponentBuilder(RouterBuilder::class)) {
            return $appBuilder;
        }

        return $appBuilder->withComponentBuilder(new RouterBuilderProxy(fn () => $this->container->resolve(RouterBuilder::class)));
    }

    /**
     * Registers Aphiria serializer component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return IApplicationBuilder For chaining
     */
    private function withSerializerComponent(IApplicationBuilder $appBuilder): IApplicationBuilder
    {
        if ($appBuilder->hasComponentBuilder(SerializerBuilder::class)) {
            return $appBuilder;
        }

        return $appBuilder->withComponentBuilder(new SerializerBuilderProxy(fn () => $this->container->resolve(SerializerBuilder::class)));
    }

    /**
     * Registers the Aphiria validation component
     *
     * @param IApplicationBuilder $appBuilder The app builder to decorate
     * @return IApplicationBuilder For chaining
     */
    private function withValidatorComponent(IApplicationBuilder $appBuilder): IApplicationBuilder
    {
        if ($appBuilder->hasComponentBuilder(ValidatorBuilder::class)) {
            return $appBuilder;
        }

        return $appBuilder->withComponentBuilder(new ValidatorBuilderProxy(fn () => $this->container->resolve(ValidatorBuilder::class)));
    }
}
