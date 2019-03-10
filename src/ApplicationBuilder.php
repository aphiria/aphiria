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
use Closure;
use Opulence\Ioc\Bootstrappers\IBootstrapperRegistry;

/**
 * Defines an application builder
 */
class ApplicationBuilder implements IApplicationBuilder
{
    /** @var IBootstrapperRegistry The bootstrappers that will be passed to bootstrapper delegates */
    private $bootstrappers;
    /** @var RouteBuilderRegistry The route builders to use in delegates */
    private $routeBuilders;
    /** @var CommandRegistry The command registry to use in delegates */
    private $commands;
    /** @var Closure[] The list of bootstrapper delegates */
    private $bootstrapperDelegates = [];
    /** @var CLosure[] The list of route delegates */
    private $routeDelegates = [];
    /** @var Closure[] The list of command delegates */
    private $commandDelegates = [];

    /**
     * @param IBootstrapperRegistry $bootstrappers The bootstrappers that will be passed to bootstrapper delegates
     * @param RouteBuilderRegistry $routeBuilders The route builders to use in delegates
     * @param CommandRegistry $commands The command registry to use in delegates
     */
    public function __construct(
        IBootstrapperRegistry $bootstrappers,
        RouteBuilderRegistry $routeBuilders,
        CommandRegistry $commands
    ) {
        $this->bootstrappers = $bootstrappers;
        $this->routeBuilders = $routeBuilders;
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

        foreach ($this->routeDelegates as $routeDelegate) {
            $routeDelegate($this->routeBuilders);
        }

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
