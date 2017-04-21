<?php
namespace Opulence\Router\Matchers;

use Opulence\Router\MatchedRoute;
use Opulence\Router\Route;
use Opulence\Router\RouteCollection;
use Opulence\Router\RouteNotFoundException;

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
    public function match(
        string $httpMethod,
        string $host,
        string $path,
        array $headers,
        RouteCollection $routes
    ) : MatchedRoute {
        $hostAndPath = $host . $path;
        $uppercaseHttpMethod = strtoupper($httpMethod);
        $routesByMethod = $routes->getByMethod($uppercaseHttpMethod);

        foreach (array_chunk($routesByMethod, self::ROUTE_CHUNK_SIZE, true) as $chunkedRoutes) {
            /** @var Route[] $routesByCapturingGroupOffsets */
            $routesByCapturingGroupOffsets = [];
            $capturingGroupOffset = 0;
            $regexes = [];

            foreach ($chunkedRoutes as $route) {
                $routesByCapturingGroupOffsets[$capturingGroupOffset] = $route;
                $uriTemplate = $route->getUriTemplate();
                $capturingGroupOffset += count($uriTemplate->getRouteVarNames()) + 1;
                $regexes[] = $uriTemplate->getRegex();
            }

            $matches = [];

            if (preg_match('#^(?:(' . implode(')|(', $regexes) . '))$#', $hostAndPath, $matches) !== 1) {
                continue;
            }

            // Remove the subject of the matches
            array_shift($matches);

            foreach ($routesByCapturingGroupOffsets as $offset => $route) {
                if ($matches[$offset] === '') {
                    continue;
                }

                $uriTemplate = $route->getUriTemplate();

                if (!$this->headersMatch($route->getHeadersToMatch(), $headers)) {
                    continue;
                }

                // Since the first value in this route's capturing group is the entire matched route,
                // start with the next offset, which will contain the route variables
                $matchedRouteVarValues = array_slice($matches, $offset + 1, count($uriTemplate->getRouteVarNames()));
                $routeVars = [];
                $this->populateRouteVars(
                    $routeVars,
                    $uriTemplate->getRouteVarNames(),
                    $matchedRouteVarValues,
                    $uriTemplate->getDefaultRouteVars()
                );

                if (!$this->routeVarsPassRules($routeVars, $uriTemplate->getRouteVarRules())) {
                    continue;
                }

                return new MatchedRoute(
                    $route->getAction(),
                    $routeVars,
                    $route->getMiddlewareBindings()
                );
            }
        }

        throw new RouteNotFoundException();
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
     * @param array $routeVarNames The list of route var names to expect
     * @param array $matchedRouteVarValues The matches from the regex
     * @param array $defaultRouteVars The mapping of variable names to their default values
     */
    private function populateRouteVars(
        array &$routeVars,
        array $routeVarNames,
        array $matchedRouteVarValues,
        array $defaultRouteVars
    ) : void {
        $routeVars = [];

        // Set any missing route vars to their default values, if they have any
        foreach ($defaultRouteVars as $name => $defaultValue) {
            $routeVarIndex = array_search($name, $routeVarNames);

            if (!isset($matchedRouteVarValues[$routeVarIndex])) {
                $matchedRouteVarValues[$routeVarIndex] = $defaultValue;
            }
        }

        foreach ($matchedRouteVarValues as $matchIndex => $value) {
            $routeVars[$routeVarNames[$matchIndex]] = $value;
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
