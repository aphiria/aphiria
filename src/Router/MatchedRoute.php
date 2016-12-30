<?php
namespace Opulence\Router;

use Closure;

/**
 * Defines a matched route
 */
class MatchedRoute
{
    /** @var Closure The action that the route performs */
    private $action = null;
    /** @var array The mapping of route variables to their values */
    private $routeVars = [];
    /** @var array The list of middleware on this route */
    private $middleware = [];
    
    public function __construct(Closure $action, array $routeVars, array $middleware)
    {
        $this->action = $action;
        $this->routeVars = $routeVars;
        $this->middleware = $middleware;
    }
    
    public function getAction() : Closure
    {
        return $this->action;
    }
    
    public function getMiddleware() : array
    {
        return $this->middleware;
    }
    
    public function getRouteVars() : array
    {
        return $this->routeVars;
    }
}