<?php
namespace Opulence\Router\Matchers;

use Opulence\Router\MatchedRoute;
use Opulence\Router\Route;
use Opulence\Router\RouteCollection;

/**
 * Defines a route matcher
 */
class RouteMatcher implements IRouteMatcher
{
    /** @var The number of routes to attempt to match against in one go */
    private const ROUTE_CHUNK_SIZE = 10;
    
    /**
     * @inheritdoc
     */
    public function tryMatch(
        string $httpMethod,
        string $host,
        string $path,
        array $headers,
        RouteCollection $routes,
        ?MatchedRoute &$matchedRoute
    ) : bool {
        $hostAndPath = $host . $path;
        $uppercaseHttpMethod = strtoupper($httpMethod);
        $routesByMethod = $routes->getByMethod($uppercaseHttpMethod);

        foreach (array_chunk($routesByMethod, self::ROUTE_CHUNK_SIZE) as $chunkedRoutes) {
            /** @var Route[] $routesByCapturingGroupOffsets */
            $routesByCapturingGroupOffsets = [];
            $capturingGroupOffset = 1;
            $regexes = [];
            
            foreach ($chunkedRoutes as $route) {
                $routesByCapturingGroupOffsets[$capturingGroupOffset] = $route;
                $capturingGroupOffset += $route->getUriTemplate()->getNumCapturingGroups();
                $regexes[] = $route->getRegex();
            }
            
            $matches = [];
            
            if (preg_match('#^(?:' . implode('|', $regexes) . ')$#', $hostAndPath, $matches) !== 1) {
                continue;
            }
            
            foreach ($routesByCapturingGroupOffsets as $offset => $route) {
                if ($matches[$offset] === '') {
                    continue;
                }
                
                $uriTemplate = $route->getUriTemplate();
                
                if (!$this->headersMatch($route->getHeadersToMatch(), $headers)) {
                    continue;
                }
                
                $matchedGroups = array_slice($matches, $i, $uriTemplate->getNumCapturingGroups());
                $routeVars = [];
                $this->populateRouteVars($routeVars, $matchedGroups, $uriTemplate->getDefaultRouteVars());
                
                if (!$this->routeVarsPassRules($routeVars, $uriTemplate->getRouteVarRules())) {
                    continue;
                }
                
                $matchedRoute = new MatchedRoute(
                    $route->getAction(),
                    $routeVars,
                    $route->getMiddlewareBindings()
                );
                
                return true;
            }
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
                !isset($allHeaders[$uppercaseHeaderNameToMatch]) ||
                $allHeaders[$uppercaseHeaderNameToMatch] !== $headerValueToMatch
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Populates route vars from matches in the regex
     *
     * @param array $routeVars The route vars to populate
     * @param array $matches The matches from the regex
     * @param array $defaultRouteVars The mapping of variable names to their default values
     */
    private function populateRouteVars(array &$routeVars, array $matches, array $defaultRouteVars) : void
    {
        $routeVars = [];

        // Remove the subject
        array_shift($matches);

        // Set any missing route vars to their default values, if they have any
        foreach ($defaultRouteVars as $name => $defaultValue) {
            if (!isset($matches[$name])) {
                $matches[$name] = $defaultValue;
            }
        }

        // The matches will also contain numerical indices - we don't care about them
        foreach ($matches as $name => $value) {
            if (is_string($name)) {
                $routeVars[$name] = $value;
            }
        }
    }

    /**
     * Gets whether or not the route vars pass all the registered rules
     *
     * @param array $routeVars The route vars to validate
     * @param IRule[][] $routeVarRules The mapping of route var names to their rules
     * @return bool True if all the route vars pass their rules, otherwise false
     */
    private function routeVarsPassRules(array $routeVars, array $routeVarRules) : bool
    {
        foreach ($routeVarRules as $name => $rules) {
            foreach ($rules as $rule) {
                if (isset($routeVars[$name]) && !$rule->passes($routeVars[$name])) {
                    return false;
                }
            }
        }

        return true;
    }
}
