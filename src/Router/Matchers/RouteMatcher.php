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
    public function tryMatch(
        string $httpMethod,
        string $uri,
        array $headers,
        RouteCollection $routes,
        MatchedRoute &$matchedRoute
    ) : bool {
        $uppercaseHttpMethod = strtoupper($httpMethod);
        $routesByMethod = $routes->getByMethod($uppercaseHttpMethod);

        foreach ($routesByMethod as $route) {
            if (
                !$route->getUriTemplate()->tryMatch($uri, $routeVars = null)
                || !$this->headersMatch($route->getHeadersToMatch(), $headers)
            ) {
                continue;
            }

            $matchedRoute = new MatchedRoute($route->getAction(), $routeVars, $route->getMiddlewareBindings());

            return true;
        }

        return false;
    }

    /**
     * Compares the headers to match on against all the headers
     *
     * @param array $headersToMatch The headers to match on
     * @param array $allHeaders The list of all headers
     * @return bool True if the headers in a route match the headers from the request, otherwise false
     */
    private function headersMatch(array $headersToMatch, array $allHeaders) : bool
    {
        foreach ($headersToMatch as $headerNameToMatch => $headerValueToMatch) {
            // The header names are case-insensitive
            $uppercaseHeaderNameToMatch = strtoupper($headerNameToMatch);

            if (
                !isset($allHeaders[$uppercaseHeaderNameToMatch])
                || $allHeaders[$uppercaseHeaderNameToMatch] !== $headerValueToMatch
            ) {
                return false;
            }
        }

        return true;
    }
}
