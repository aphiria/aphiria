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
use Aphiria\Configuration\Framework\Exceptions\Builders\ExceptionHandlerBuilder;
use Aphiria\Configuration\Framework\Routing\Builders\RouterBuilder;
use Aphiria\Configuration\Framework\Serialization\Builders\EncoderBuilder;
use Aphiria\Configuration\Framework\Validation\Builders\ObjectConstraintsBuilder;
use Aphiria\DependencyInjection\IContainer;

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
        $appBuilder->enqueueComponentBuilderCall(
            CommandBuilder::class,
            fn (CommandBuilder $commandBuilder) => $commandBuilder->withAnnotations()
        );

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
        $appBuilder->withComponentBuilder(
            EncoderBuilder::class,
            fn () => $this->container->resolve(EncoderBuilder::class)
        );

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
        $appBuilder->withComponentBuilder(
            ExceptionHandlerBuilder::class,
            fn () => $this->container->resolve(ExceptionHandlerBuilder::class)
        );

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
        $appBuilder->enqueueComponentBuilderCall(
            RouterBuilder::class,
            fn (RouterBuilder $routerBuilder) => $routerBuilder->withAnnotations()
        );

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
        $appBuilder->withComponentBuilder(
            RouterBuilder::class,
            fn () => $this->container->resolve(RouterBuilder::class)
        );

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
        $appBuilder->enqueueComponentBuilderCall(
            ObjectConstraintsBuilder::class,
            fn (ObjectConstraintsBuilder $objectConstraintsBuilder) => $objectConstraintsBuilder->withAnnotations()
        );

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
        $appBuilder->withComponentBuilder(
            ObjectConstraintsBuilder::class,
            fn () => $this->container->resolve(ObjectConstraintsBuilder::class)
        );

        return $this;
    }
}
