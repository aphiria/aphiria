<?php
namespace Opulence\Router;

use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\RouteAction;

/**
 * Defines a matched route
 */
class MatchedRoute
{
    /** @var RouteAction The action that the route performs */
    private $action = null;
    /** @var array The mapping of route variables to their values */
    private $routeVars = [];
    /** @var MiddlewareBinding[] The list of middleware bindings on this route */
    private $middlewareBindings = [];

    /**
     *
     * @param RouteAction $action The action taken on this route
     * @param array $routeVars The mapping of route var names to their values
     * @param MiddlewareBinding[] $middlewareBindings The list of middleware bindings
     */
    public function __construct(RouteAction $action, array $routeVars, array $middlewareBindings)
    {
        $this->action = $action;
        $this->routeVars = $routeVars;
        $this->middlewareBindings = $middlewareBindings;
    }

    /**
     * @return RouteAction
     */
    public function getAction() : RouteAction
    {
        return $this->action;
    }

    /**
     * @return MiddlewareBinding[]
     */
    public function getMiddlewareBindings() : array
    {
        return $this->middlewareBindings;
    }

    /**
     * @return array
     */
    public function getRouteVars() : array
    {
        return $this->routeVars;
    }
}
