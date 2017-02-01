<?php
namespace Opulence\Router;

/**
 * Defines a matched route
 */
class MatchedRoute
{
    /** @var RouteAction The action that the route performs */
    private $action = null;
    /** @var array The mapping of route variables to their values */
    private $routeVars = [];
    /** @var array The list of middleware on this route */
    private $middleware = [];

    public function __construct(RouteAction $action, array $routeVars, array $middleware)
    {
        $this->action = $action;
        $this->routeVars = $routeVars;
        $this->middleware = $middleware;
    }

    public function getAction() : RouteAction
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
