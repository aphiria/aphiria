<?php
namespace Opulence\Router;

use Opulence\Router\Dispatchers\IRouteDispatcher;

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
    
    public function __construct(array $routes, IRouteMatcher $routeMatcher, IRouteDispatcher $routeDispatcher)
    {
        $this->routes = $routes;
        $this->routeMatcher = $routeMatcher;
        $this->routeDispatcher = $routeDispatcher;
    }
    
    public function route($request)
    {
        if ($this->routeMatcher->tryMatch($request, $this->routes, $matchedRoute = null)) {
            return $this->routeDispatcher->dispatch($request, $matchedRoute);
        }
        
        throw new RouteNotFoundException();
    }
}