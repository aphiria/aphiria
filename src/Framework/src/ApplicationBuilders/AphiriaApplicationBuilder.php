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

use Aphiria\ApplicationBuilders\ApplicationBuilder;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Console\Builders\CommandBuilder;
use Aphiria\Framework\DependencyInjection\Builders\BootstrapperBuilder;
use Aphiria\Framework\Exceptions\Builders\ExceptionHandlerBuilder;
use Aphiria\Framework\Middleware\Builders\MiddlewareBuilder;
use Aphiria\Framework\Routing\Builders\RouterBuilder;
use Aphiria\Framework\Serialization\Builders\SerializerBuilder;
use Aphiria\Framework\Validation\Builders\ValidatorBuilder;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Serialization\Encoding\IEncoder;
use Closure;

/**
 * Defines the application builder for Aphiria applications
 */
abstract class AphiriaApplicationBuilder extends ApplicationBuilder implements IAphiriaApplicationBuilder
{
    /** @var IContainer The DI container to resolve dependencies with */
    protected IContainer $container;
    /** @var IBootstrapperDispatcher The dispatcher for bootstrappers */
    protected IBootstrapperDispatcher $bootstrapperDispatcher;

    /**
     * @param IContainer $container The DI container to resolve dependencies with
     * @param IBootstrapperDispatcher $bootstrapperDispatcher The dispatcher for bootstrappers
     */
    public function __construct(IContainer $container, IBootstrapperDispatcher $bootstrapperDispatcher)
    {
        $this->container = $container;
        $this->bootstrapperDispatcher = $bootstrapperDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function withBootstrappers($bootstrappers): self
    {
        $this->withBootstrapperComponent()
            ->configureComponentBuilder(
            BootstrapperBuilder::class,
            fn (BootstrapperBuilder $bootstrapperBuilder) => $bootstrapperBuilder->withBootstrappers($bootstrappers)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withCommandAnnotations(): self
    {
        $this->withConsoleComponent()
            ->configureComponentBuilder(
            CommandBuilder::class,
            fn (CommandBuilder $commandBuilder) => $commandBuilder->withAnnotations()
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withCommands(Closure $callback): self
    {
        $this->withConsoleComponent()
            ->configureComponentBuilder(
            CommandBuilder::class,
            fn (CommandBuilder $commandBuilder) => $commandBuilder->withCommands($callback)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withEncoder(string $class, IEncoder $encoder): self
    {
        $this->withSerializerComponent()
            ->configureComponentBuilder(
            SerializerBuilder::class,
            fn (SerializerBuilder $encoderBuilder) => $encoderBuilder->withEncoder($class, $encoder)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withExceptionResponseFactory(string $exceptionType, Closure $responseFactory): self
    {
        $this->withExceptionHandlerComponent()
            ->configureComponentBuilder(
            ExceptionHandlerBuilder::class,
            fn (ExceptionHandlerBuilder $exceptionHandlerBuilder) => $exceptionHandlerBuilder->withResponseFactory($exceptionType, $responseFactory)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withGlobalMiddleware($middlewareBindings): self
    {
        $this->withMiddlewareComponent()
            ->configureComponentBuilder(
            MiddlewareBuilder::class,
            fn (MiddlewareBuilder $middlewareBuilder) => $middlewareBuilder->withGlobalMiddleware($middlewareBindings)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withLogLevelFactory(string $exceptionType, Closure $logLevelFactory): self
    {
        $this->withExceptionHandlerComponent()
            ->configureComponentBuilder(
            ExceptionHandlerBuilder::class,
            fn (ExceptionHandlerBuilder $exceptionHandlerBuilder) => $exceptionHandlerBuilder->withLogLevelFactory($exceptionType, $logLevelFactory)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withObjectConstraints(Closure $callback): self
    {
        $this->withValidatorComponent()
            ->configureComponentBuilder(
            ValidatorBuilder::class,
            fn (ValidatorBuilder $objectConstraintsBuilder) => $objectConstraintsBuilder->withObjectConstraints($callback)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withRouteAnnotations(): self
    {
        $this->withRouterComponent()
            ->configureComponentBuilder(
            RouterBuilder::class,
            fn (RouterBuilder $routerBuilder) => $routerBuilder->withAnnotations()
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withRoutes(Closure $callback): self
    {
        $this->withRouterComponent()
            ->configureComponentBuilder(
            RouterBuilder::class,
            fn (RouterBuilder $routerBuilder) => $routerBuilder->withRoutes($callback)
        );

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withValidatorAnnotations(): self
    {
        $this->withValidatorComponent()
            ->configureComponentBuilder(
            ValidatorBuilder::class,
            fn (ValidatorBuilder $objectConstraintsBuilder) => $objectConstraintsBuilder->withAnnotations()
        );

        return $this;
    }

    /**
     * Registers the bootstrapper component
     *
     * @return self For chaining
     */
    private function withBootstrapperComponent(): self
    {
        if (isset($this->componentBuilderFactories[BootstrapperBuilder::class])) {
            return $this;
        }

        $this->withComponentBuilder(
            BootstrapperBuilder::class,
            fn () => new BootstrapperBuilder($this->bootstrapperDispatcher)
        );

        return $this;
    }

    /**
     * Registers the console component
     *
     * @return self For chaining
     */
    private function withConsoleComponent(): self
    {
        if (isset($this->componentBuilderFactories[CommandBuilder::class])) {
            return $this;
        }

        // Bind the command registry here so that it can be injected into the component builder
        $this->container->bindInstance(CommandRegistry::class, new CommandRegistry());
        $this->withComponentBuilder(
            CommandBuilder::class,
            fn () => $this->container->resolve(CommandBuilder::class)
        );

        return $this;
    }

    /**
     * Registers the Aphiria exception handler component
     *
     * @return self For chaining
     */
    private function withExceptionHandlerComponent(): self
    {
        if (isset($this->componentBuilderFactories[ExceptionHandlerBuilder::class])) {
            return $this;
        }

        $this->withComponentBuilder(
            ExceptionHandlerBuilder::class,
            fn () => $this->container->resolve(ExceptionHandlerBuilder::class)
        );

        return $this;
    }

    /**
     * Registers the middleware component
     *
     * @return self For chaining
     */
    private function withMiddlewareComponent(): self
    {
        if (isset($this->componentBuilderFactories[MiddlewareBuilder::class])) {
            return $this;
        }

        // Bind the middleware collection here so that it can be injected into the component builder
        $this->container->hasBinding(MiddlewareCollection::class)
            ? $middlewareCollection= $this->container->resolve(MiddlewareCollection::class)
            : $this->container->bindInstance(MiddlewareCollection::class, $middlewareCollection = new MiddlewareCollection());

        $this->withComponentBuilder(
            MiddlewareBuilder::class,
            fn () => new MiddlewareBuilder($middlewareCollection, $this->container)
        );

        return $this;
    }

    /**
     * Registers the Aphiria routing component
     *
     * @return self For chaining
     */
    private function withRouterComponent(): self
    {
        if (isset($this->componentBuilderFactories[RouterBuilder::class])) {
            return $this;
        }

        $this->withComponentBuilder(
            RouterBuilder::class,
            fn () => $this->container->resolve(RouterBuilder::class)
        );

        return $this;
    }

    /**
     * Registers Aphiria serializer component
     *
     * @return self For chaining
     */
    private function withSerializerComponent(): self
    {
        if (isset($this->componentBuilderFactories[SerializerBuilder::class])) {
            return $this;
        }

        $this->withComponentBuilder(
            SerializerBuilder::class,
            fn () => $this->container->resolve(SerializerBuilder::class)
        );

        return $this;
    }

    /**
     * Registers the Aphiria validation component
     *
     * @return self For chaining
     */
    private function withValidatorComponent(): self
    {
        if (isset($this->componentBuilderFactories[ValidatorBuilder::class])) {
            return $this;
        }

        $this->withComponentBuilder(
            ValidatorBuilder::class,
            fn () => $this->container->resolve(ValidatorBuilder::class)
        );

        return $this;
    }
}
