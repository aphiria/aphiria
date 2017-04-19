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
     * @param string $host The host of the request
     * @param string $path The path of the request
     * @param array $headers The list of headers in the request
     * @return MatchedRoute The matched route
     * @throws RouteNotFoundException Thrown if no matching route was found
     */
    public function route(string $httpMethod, string $host, string $path, array $headers = []) : MatchedRoute;
}
