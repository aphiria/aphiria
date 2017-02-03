<?php
namespace Opulence\Router\Matchers;

use Opulence\Router\MatchedRoute;
use Opulence\Router\RouteCollection;

/**
 * Defines a route matcher
 */
class RouteMatcher implements IRouteMatcher
{
    /**
     * @inheritdoc
     */
    public function tryMatch(string $httpMethod, string $uri, RouteCollection $routes, MatchedRoute &$matchedRoute) : bool
    {
        $uppercaseHttpMethod = strtoupper($httpMethod);
        $routesByMethod = $routes->getByMethod($uppercaseHttpMethod);
        $routeIsHttps = parse_url($uri, PHP_URL_SCHEME) === 'https';

        foreach ($routesByMethod as $route) {
            if (!$route->getUriTemplate()->tryMatch($uri, $routeVars = null)) {
                continue;
            }

            if ($route->isHttpsOnly() && !$routeIsHttps) {
                continue;
            }

            $matchedRoute = new MatchedRoute($route->getAction(), $routeVars, $route->getMiddlewareBindings());

            return true;
        }

        return false;
    }
}
