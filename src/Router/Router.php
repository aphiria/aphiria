<?php
namespace Opulence\Router;

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
    /** @var Route[] The list of routes */
    private $routes = [];
    /** @var IRouteMatcher The route matcher */
    private $routeMatcher = null;
    /** @var IRouteDispatcher The route dispatcher */
    private $routeDispatcher = null;

    public function __construct(
        array $routes,
        IRouteMatcher $routeMatcher = null,
        IRouteDispatcher $routeDispatcher = null
    ) {
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