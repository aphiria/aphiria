<?php
namespace Opulence\Router;

/**
 * Defines the interface for routers to implement
 */
interface IRouter
{
    /**
     * Routes the request to a route
     *
     * @param string $httpMethod The HTTP method in the request
     * @param string $uri The URI of the request
     * @return MatchedRoute The matched route
     * @throws RouteNotFoundException Thrown if no matching route was found
     */
    public function route(string $httpMethod, string $uri) : MatchedRoute;
}
