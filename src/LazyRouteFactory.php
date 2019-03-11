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
    /** @var Closure[] The list of delegates that will actually create the routes */
    private $routeFactoryDelegates = [];

    /**
     * @param Closure|null $routeFactoryDelegate The initial delegate that will be used to create routes
     *      Note: Must be parameterless and return a list of Route objects
     */
    public function __construct(Closure $routeFactoryDelegate = null)
    {
        if ($routeFactoryDelegate !== null) {
            $this->routeFactoryDelegates[] = $routeFactoryDelegate;
        }
    }

    /**
     * Adds a factory delegate
     *
     * @param Closure $routeFactoryDelegate The delegate to add
     *      Note: Must be parameterless and return a list of Route objects
     */
    public function addFactoryDelegate(Closure $routeFactoryDelegate): void
    {
        $this->routeFactoryDelegates[] = $routeFactoryDelegate;
    }

    /**
     * @inheritdoc
     */
    public function createRoutes(): RouteCollection
    {
        $routes = new RouteCollection();

        foreach ($this->routeFactoryDelegates as $routeFactoryDelegate) {
            $routes->addMany($routeFactoryDelegate());
        }

        return $routes;
    }
}
