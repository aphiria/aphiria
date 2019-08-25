<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

use Aphiria\Routing\Caching\IRouteCache;
use Closure;

/**
 * Defines a route factory that lazily creates routes when necessary
 */
final class LazyRouteFactory implements IRouteFactory
{
    /** @var Closure[] The list of factories that will actually create the routes */
    private array $routeFactories = [];
    /** @var IRouteCache|null The optional route cache to store compiled routes in */
    private ?IRouteCache $routeCache;

    /**
     * @param Closure|null $routeFactory The initial factory that will be used to create routes
     *      Note: Must be parameterless and return a list of Route objects
     * @param IRouteCache|null $routeCache The route cache, if we're using a cache, otherwise null
     */
    public function __construct(Closure $routeFactory = null, IRouteCache $routeCache = null)
    {
        if ($routeFactory !== null) {
            $this->routeFactories[] = $routeFactory;
        }

        $this->routeCache = $routeCache;
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
        if ($this->routeCache !== null && ($routes = $this->routeCache->get()) !== null) {
            return $routes;
        }

        $routes = new RouteCollection();

        foreach ($this->routeFactories as $routeFactory) {
            $routes->addMany($routeFactory());
        }

        // Save this to cache for next time
        if ($this->routeCache !== null) {
            $this->routeCache->set($routes);
        }

        return $routes;
    }
}
