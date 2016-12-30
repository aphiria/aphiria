<?php
namespace Opulence\Router;

/**
 * Defines a route matcher
 */
class RouteMatcher implements IRouteMatcher
{
    public function tryMatch($request, array $routes, MatchedRoute &$matchedRoute) : bool
    {
        /** @var Route $route */
        foreach ($routes as $route) {
            if (!in_array($route->getHttpMethods(), $request->getMethod())) {
                continue;
            }

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