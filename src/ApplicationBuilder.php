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
    /** @var IBootstrapperRegistry The bootstrappers that will be passed to bootstrapper callbacks */
    private $bootstrappers;
    /** @var LazyRouteFactory The factory that will create our routes */
    private $routeFactory;
    /** @var CommandRegistry The command registry to use in callbacks */
    private $commands;
    /** @var Closure[] The list of bootstrapper callbacks */
    private $bootstrapperCallbacks = [];
    /** @var Closure[] The list of route callbacks */
    private $routeCallbacks = [];
    /** @var Closure[] The list of command callbacks */
    private $commandCallbacks = [];

    /**
     * @param IBootstrapperRegistry $bootstrappers The bootstrappers that will be passed to bootstrapper callbacks
     * @param LazyRouteFactory $routeFactory The factory that will create our routes
     * @param CommandRegistry $commands The command registry to use in callbacks
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
        foreach ($this->bootstrapperCallbacks as $bootstrapperCallback) {
            $bootstrapperCallback($this->bootstrappers);
        }

        $this->routeFactory->addFactory(function () {
            $routeBuilders = new RouteBuilderRegistry();

            foreach ($this->routeCallbacks as $routeCallback) {
                $routeCallback($routeBuilders);
            }

            return $routeBuilders->buildAll();
        });

        foreach ($this->commandCallbacks as $commandCallback) {
            $commandCallback($this->commands);
        }
    }

    /**
     * @inheritdoc
     */
    public function withBootstrappers(Closure $callback): IApplicationBuilder
    {
        $this->bootstrapperCallbacks[] = $callback;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withCommands(Closure $callback): IApplicationBuilder
    {
        $this->commandCallbacks[] = $callback;

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
    public function withRoutes(Closure $callback): IApplicationBuilder
    {
        $this->routeCallbacks[] = $callback;

        return $this;
    }
}
