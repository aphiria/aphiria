<?php
namespace Opulence\Router;

use Opulence\Router\Middleware\MiddlewareMetadata;

/**
 * Defines a matched route
 */
class MatchedRoute
{
    /** @var RouteAction The action that the route performs */
    private $action = null;
    /** @var array The mapping of route variables to their values */
    private $routeVars = [];
    /** @var MiddlewareMetadata[] The list of middleware metadata on this route */
    private $middlewareMetadata = [];

    public function __construct(RouteAction $action, array $routeVars, array $middlewareMetadata)
    {
        $this->action = $action;
        $this->routeVars = $routeVars;
        $this->middlewareMetadata = $middlewareMetadata;
    }

    public function getAction() : RouteAction
    {
        return $this->action;
    }

    /**
     * @return MiddlewareMetadata[]
     */
    public function getMiddlewareMetadata() : array
    {
        return $this->middlewareMetadata;
    }

    public function getRouteVars() : array
    {
        return $this->routeVars;
    }
}
