<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

use Closure;

/**
 * Defines a route factory that lazily creates routes when necessary
 */
final class LazyRouteFactory implements IRouteFactory
{
    /** @var Closure[] The list of factories that will actually create the routes */
    private $routeFactories = [];

    /**
     * @param Closure|null $routeFactory The initial factory that will be used to create routes
     *      Note: Must be parameterless and return a list of Route objects
     */
    public function __construct(Closure $routeFactory = null)
    {
        if ($routeFactory !== null) {
            $this->routeFactories[] = $routeFactory;
        }
    }

    /**
     * Adds a route factory
     *
     * @param Closure $routeFactory The factory to add
     *      Note: Must be parameterless and return a list of Route objects
     */
    public function addFactory(Closure $routeFactory): void
    {
        $this->routeFactories[] = $routeFactory;
    }

    /**
     * @inheritdoc
     */
    public function createRoutes(): RouteCollection
    {
        $routes = new RouteCollection();

        foreach ($this->routeFactories as $routeFactory) {
            $routes->addMany($routeFactory());
        }

        return $routes;
    }
}
