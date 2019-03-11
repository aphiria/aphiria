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

use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\LazyRouteFactory;
use Closure;
use Opulence\Ioc\Bootstrappers\IBootstrapperRegistry;

/**
 * Defines an application builder
 */
class ApplicationBuilder implements IApplicationBuilder
{
    /** @var IBootstrapperRegistry The bootstrappers that will be passed to bootstrapper delegates */
    private $bootstrappers;
    /** @var LazyRouteFactory The factory that will create our routes */
    private $routeFactory;
    /** @var CommandRegistry The command registry to use in delegates */
    private $commands;
    /** @var Closure[] The list of bootstrapper delegates */
    private $bootstrapperDelegates = [];
    /** @var Closure[] The list of route delegates */
    private $routeDelegates = [];
    /** @var Closure[] The list of command delegates */
    private $commandDelegates = [];

    /**
     * @param IBootstrapperRegistry $bootstrappers The bootstrappers that will be passed to bootstrapper delegates
     * @param LazyRouteFactory $routeFactory The factory that will create our routes
     * @param CommandRegistry $commands The command registry to use in delegates
     */
    public function __construct(
        IBootstrapperRegistry $bootstrappers,
        LazyRouteFactory $routeFactory,
        CommandRegistry $commands
    ) {
        $this->bootstrappers = $bootstrappers;
        $this->routeFactory = $routeFactory;
        $this->commands = $commands;
    }

    /**
     * @inheritdoc
     */
    public function build(): void
    {
        foreach ($this->bootstrapperDelegates as $bootstrapperDelegate) {
            $bootstrapperDelegate($this->bootstrappers);
        }

        $this->routeFactory->addFactoryDelegate(function () {
            $routeBuilders = new RouteBuilderRegistry();

            foreach ($this->routeDelegates as $routeDelegate) {
                $routeDelegate($routeBuilders);
            }

            return $routeBuilders->buildAll();
        });

        foreach ($this->commandDelegates as $commandDelegate) {
            $commandDelegate($this->commands);
        }
    }

    /**
     * @inheritdoc
     */
    public function withBootstrappers(Closure $delegate): IApplicationBuilder
    {
        $this->bootstrapperDelegates[] = $delegate;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withCommands(Closure $delegate): IApplicationBuilder
    {
        $this->commandDelegates[] = $delegate;

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
    public function withRoutes(Closure $delegate): IApplicationBuilder
    {
        $this->routeDelegates[] = $delegate;

        return $this;
    }
}
