<?php
namespace Opulence\Router\Matchers;

use Opulence\Router\Routes\RouteCollection;

/**
 * Defines a route matcher
 */
class RouteMatcher implements IRouteMatcher
{
    public function tryMatch($request, RouteCollection $routes, MatchedRoute &$matchedRoute) : bool
    {
        $routesByMethod = $routes->get($request->getHttpMethod());
        
        foreach ($routesByMethod as $route) {
            if (!$route->getPathTemplate()->tryMatch($request->getPath(), $routeVars)) {
                continue;
            }

            if (!$route->getPathTemplate()->tryMatch($request->getHost(), $routeVars)) {
                continue;
            }

            if ($route->isHttpsOnly() && !$request->isSecure()) {
                continue;
            }

            $matchedRoute = new MatchedRoute($route->getAction(), $routeVars, $route->getMiddleware());

            return true;
        }

        return false;
    }
}