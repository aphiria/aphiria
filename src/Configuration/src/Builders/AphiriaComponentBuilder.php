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

use Aphiria\Configuration\Framework\Console\Builders\CommandBuilder;
use Aphiria\Configuration\Framework\DependencyInjection\Builders\BootstrapperBuilder;
use Aphiria\Configuration\Framework\Exceptions\Builders\ExceptionHandlerBuilder;
use Aphiria\Configuration\Framework\Middleware\Builders\MiddlewareBuilder;
use Aphiria\Configuration\Framework\Routing\Builders\RouterBuilder;
use Aphiria\Configuration\Framework\Serialization\Builders\EncoderBuilder;
use Aphiria\Configuration\Framework\Validation\Builders\ObjectConstraintsBuilder;
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Middleware\MiddlewareCollection;

/**
 * Defines the component builder for Aphiria components
 */
final class AphiriaComponentBuilder
{
    /** @var IContainer The DI container to resolve dependencies with */
    private IContainer $container;
    /** @var IBootstrapperDispatcher The dispatcher for bootstrappers */
    private IBootstrapperDispatcher $bootstrapperDispatcher;

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
     * Registers the bootstrapper component
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withBootstrapperComponent(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->configureComponentBuilder('',fn (BootstrapperBuilder $bootstrapperBuilder) => $bootstrapperBuilder->withManyBootstrappers([]));

        $appBuilder->withComponentBuilder(
            BootstrapperBuilder::class,
            fn () => new BootstrapperBuilder($this->bootstrapperDispatcher)
        );

        return $this;
    }

    /**
     * Enables console command annotations
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withConsoleAnnotations(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->configureComponentBuilder(
            CommandBuilder::class,
            fn (CommandBuilder $commandBuilder) => $commandBuilder->withAnnotations()
        );

        return $this;
    }

    /**
     * Registers Aphiria encoder component
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withEncoderComponent(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->withComponentBuilder(
            EncoderBuilder::class,
            fn () => $this->container->resolve(EncoderBuilder::class)
        );

        return $this;
    }

    /**
     * Registers the Aphiria exception handler component
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withExceptionHandlerComponent(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->withComponentBuilder(
            ExceptionHandlerBuilder::class,
            fn () => $this->container->resolve(ExceptionHandlerBuilder::class)
        );

        return $this;
    }

    /**
     * Registers the middleware component
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withMiddlewareComponent(IApplicationBuilder $appBuilder): self
    {
        $this->container->bindInstance(MiddlewareCollection::class, $middlewareCollection = new MiddlewareCollection());
        $appBuilder->withComponentBuilder(
            MiddlewareBuilder::class,
            fn () => new MiddlewareBuilder($middlewareCollection, $this->container)
        );

        return $this;
    }

    /**
     * Enables routing annotations
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withRoutingAnnotations(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->configureComponentBuilder(
            RouterBuilder::class,
            fn (RouterBuilder $routerBuilder) => $routerBuilder->withAnnotations()
        );

        return $this;
    }

    /**
     * Registers the Aphiria routing component
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withRoutingComponent(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->withComponentBuilder(
            RouterBuilder::class,
            fn () => $this->container->resolve(RouterBuilder::class)
        );

        return $this;
    }

    /**
     * Enables Aphiria validation annotations
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withValidationAnnotations(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->configureComponentBuilder(
            ObjectConstraintsBuilder::class,
            fn (ObjectConstraintsBuilder $objectConstraintsBuilder) => $objectConstraintsBuilder->withAnnotations()
        );

        return $this;
    }

    /**
     * Registers the Aphiria validation component
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withValidationComponent(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->withComponentBuilder(
            ObjectConstraintsBuilder::class,
            fn () => $this->container->resolve(ObjectConstraintsBuilder::class)
        );

        return $this;
    }
}
