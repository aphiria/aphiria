<?php
namespace Opulence\Router\Matchers;

use Opulence\Router\MatchedRoute;
use Opulence\Router\RouteCollection;

/**
 * Defines a route matcher
 */
class RouteMatcher implements IRouteMatcher
{
    public function tryMatch($request, RouteCollection $routes, MatchedRoute &$matchedRoute) : bool
    {
        $routesByMethod = $routes->getByMethod($request->getHttpMethod());

        foreach ($routesByMethod as $route) {
            if (!$route->getRouteTemplate()->tryMatch($request->getUri(), $routeVars)) {
                continue;
            }

            if ($route->isHttpsOnly() && !$request->isSecure()) {
                continue;
            }

            $matchedRoute = new MatchedRoute($route->getAction(), $routeVars, $route->getMiddlewareMetadata());

            return true;
        }

        return false;
    }
}
