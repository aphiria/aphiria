<?php
namespace Opulence\Router;

use Closure;
use Opulence\Router\Builders\RouteBuilderRegistry;
use Opulence\Router\Caching\IRouteCache;

/**
 * Defines a route factory
 */
class RouteFactory
{
    /** @var Closure The callback that builds routes */
    private $routeBuilderCallback = null;
    /** @var IRouteCache|null The cache that stores our routes, otherwise null if not using cache */
    private $routeCache = null;
    /** @var RouteBuilderRegistry The route builder registry to build routes with */
    private $routeBuilderRegistry = null;

    /**
     * @param Closure $routeBuilderCallback The callback that builds the routes
     *      This must accept an instance of RouteBuilderRegistry
     * @param IRouteCache|null $routeCache The route cache to use, otherwise null if not using cache
     * @param RouteBuilderRegistry|null $routeBuilderRegistry The route builder registry to build routes with
     */
    public function __construct(
        Closure $routeBuilderCallback,
        ?IRouteCache $routeCache,
        RouteBuilderRegistry $routeBuilderRegistry = null
    ) {
        $this->routeBuilderCallback = $routeBuilderCallback;
        $this->routeCache = $routeCache;
        $this->routeBuilderRegistry = $routeBuilderRegistry ?? new RouteBuilderRegistry();
    }

    /**
     * Creates the routes
     *
     * @return RouteCollection The created routes
     */
    public function createRoutes() : RouteCollection
    {
        if ($this->routeCache === null) {
            ($this->routeBuilderCallback)($this->routeBuilderRegistry);

            return $this->routeBuilderRegistry->buildAll();
        }

        if (($routes = $this->routeCache->get()) !== null) {
            return $routes;
        }

        ($this->routeBuilderCallback)($this->routeBuilderRegistry);
        $routes = $this->routeBuilderRegistry->buildAll();
        $this->routeCache->set($this->routeBuilderRegistry->buildAll());

        return $routes;
    }
}
