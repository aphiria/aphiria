<?php
namespace Opulence\Router;

use InvalidArgumentException;
use Opulence\Router\Dispatchers\IRouteDispatcher;
use Opulence\Router\Dispatchers\MiddlewarePipeline;
use Opulence\Router\Dispatchers\RouteDispatcher;
use Opulence\Router\Matchers\IRouteMatcher;
use Opulence\Router\Matchers\RouteMatcher;

/**
 * Defines the router
 */
class Router implements IRouter
{
    /** @var RouteCollection The list of routes */
    private $routes = null;
    /** @var IRouteMatcher The route matcher */
    private $routeMatcher = null;
    /** @var IRouteDispatcher The route dispatcher */
    private $routeDispatcher = null;

    /**
     * @param RouteCollection|array $routes The list of routes
     * @param IRouteMatcher|null $routeMatcher The route matcher
     * @param IRouteDispatcher|null $routeDispatcher The route dispatcher
     */
    public function __construct(
        $routes,
        IRouteMatcher $routeMatcher = null,
        IRouteDispatcher $routeDispatcher = null
    ) {
        if (is_array($routes)) {
            $this->routes = new RouteCollection();
            $this->routes->addMany($routes);
        } elseif ($routes instanceof RouteCollection) {
            $this->routes = $routes;
        } else {
            throw new InvalidArgumentException("Routes must either be an array or a RouteCollection");
        }
        
        $this->routes = $routes;
        $this->routeMatcher = $routeMatcher ?? new RouteMatcher();
        $this->routeDispatcher = $routeDispatcher ?? new RouteDispatcher(new MiddlewarePipeline());
    }

    public function route($request)
    {
        if ($this->routeMatcher->tryMatch($request, $this->routes, $matchedRoute = null)) {
            return $this->routeDispatcher->dispatch($request, $matchedRoute);
        }

        throw new RouteNotFoundException();
    }
}