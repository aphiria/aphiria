<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing;

use Closure;
use Aphiria\Routing\Builders\RouteBuilderRegistry;

/**
 * Defines a route factory
 */
final class RouteFactory
{
    /** @var Closure The callback that builds routes */
    private $routeBuilderCallback;
    /** @var RouteBuilderRegistry The route builder registry to build routes with */
    private $routeBuilderRegistry;

    /**
     * @param Closure $routeBuilderCallback The callback that builds the routes
     *      This must accept an instance of RouteBuilderRegistry
     * @param RouteBuilderRegistry|null $routeBuilderRegistry The route builder registry to build routes with
     */
    public function __construct(Closure $routeBuilderCallback, RouteBuilderRegistry $routeBuilderRegistry = null)
    {
        $this->routeBuilderCallback = $routeBuilderCallback;
        $this->routeBuilderRegistry = $routeBuilderRegistry ?? new RouteBuilderRegistry();
    }

    /**
     * Creates the routes
     *
     * @return RouteCollection The created routes
     */
    public function createRoutes(): RouteCollection
    {
        ($this->routeBuilderCallback)($this->routeBuilderRegistry);
        $routeCollection = new RouteCollection();
        $routeCollection->addMany($this->routeBuilderRegistry->buildAll());

        return $routeCollection;
    }
}
