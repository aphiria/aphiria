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

use Aphiria\Api\RouterKernel;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\LazyRouteFactory;
use Opulence\Ioc\IContainer;

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
     * Registers Aphiria console commands
     *
     * @param IApplicationBuilder $appBuilder The app builder to register to
     * @return AphiriaComponentBuilder For chaining
     */
    public function withCommandComponent(IApplicationBuilder $appBuilder): self
    {
        $appBuilder->registerComponentFactory('commands', function (array $callbacks) {
            $commands = new CommandRegistry();

            foreach ($callbacks as $callback) {
                $callback($commands);
            }
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
        // Set up the router request handler
        $appBuilder->withRouter(fn () => $this->container->resolve(RouterKernel::class));
        // Register the routing component
        $appBuilder->registerComponentFactory('routes', function (array $callbacks) {
            $this->container->hasBinding(LazyRouteFactory::class)
                ? $routeFactory = $this->container->resolve(LazyRouteFactory::class)
                : $this->container->bindInstance(LazyRouteFactory::class, $routeFactory = new LazyRouteFactory());

            $routeFactory->addFactory(function () use ($callbacks) {
                $routeBuilders = new RouteBuilderRegistry();

                foreach ($callbacks as $callback) {
                    $callback($routeBuilders);
                }

                return $routeBuilders->buildAll();
            });
        });

        return $this;
    }
}
