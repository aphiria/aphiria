<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

namespace Aphiria\Routing;

use Aphiria\Routing\Builders\RouteBuilderRegistry;

/**
 * Defines a route factory
 */
final class RouteFactory
{
    /** @var RouteBuilderRegistry The route builder registry to build routes with */
    private $routeBuilderRegistry;

    /**
     * @param RouteBuilderRegistry $routeBuilderRegistry The route builder registry to build routes with
     */
    public function __construct(RouteBuilderRegistry $routeBuilderRegistry)
    {
        $this->routeBuilderRegistry = $routeBuilderRegistry;
    }

    /**
     * Creates the routes
     *
     * @return RouteCollection The created routes
     */
    public function createRoutes(): RouteCollection
    {
        $routeCollection = new RouteCollection();
        $routeCollection->addMany($this->routeBuilderRegistry->buildAll());

        return $routeCollection;
    }
}
