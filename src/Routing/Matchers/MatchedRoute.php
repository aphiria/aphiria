<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers;

use Opulence\Routing\Matchers\Middleware\MiddlewareBinding;

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
     * Gets the action this route takes
     *
     * @return RouteAction The route's action
     */
    public function getAction() : RouteAction
    {
        return $this->action;
    }

    /**
     * Gets the list of middleware bindings for this route
     *
     * @return MiddlewareBinding[] The list of middleware bindings
     */
    public function getMiddlewareBindings() : array
    {
        return $this->middlewareBindings;
    }

    /**
     * Gets the mapping of route var names => values
     *
     * @return array The mapping of route var names => values
     */
    public function getRouteVars() : array
    {
        return $this->routeVars;
    }
}
