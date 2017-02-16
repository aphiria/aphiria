<?php
namespace Opulence\Router\Matchers;

use Opulence\Router\MatchedRoute;
use Opulence\Router\RouteCollection;

/**
 * Defines the interface for route matchers to implement
 */
interface IRouteMatcher
{
    /**
     * Tries to match a request to the list of routes
     *
     * @param string $httpMethod The HTTP method of the request
     * @param string $uri The URI of the request
     * @param RouteCollection $routes The list of routes to match against
     * @param MatchedRoute $matchedRoute The matched route, if one is found
     * @return bool True if a match was found, otherwise false
     */
    public function tryMatch(
        string $httpMethod,
        string $uri,
        RouteCollection $routes,
        MatchedRoute &$matchedRoute
    ) : bool;
}
