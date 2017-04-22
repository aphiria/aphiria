<?php
namespace Opulence\Router\Matchers;

use Opulence\Router\MatchedRoute;
use Opulence\Router\Route;
use Opulence\Router\RouteCollection;
use Opulence\Router\RouteNotFoundException;
use Opulence\Router\UriTemplates\UriTemplate;

/**
 * Defines a route matcher
 */
class RouteMatcher implements IRouteMatcher
{
    /** @var The number of routes to attempt to match against in one go */
    private const ROUTE_CHUNK_SIZE = 10;
    /** @var RouteCollection The list of routes to match against */
    private $routes = [];
    
    /**
     * @param RouteCollection $routes The list of routes to match against
     */
    public function __construct(RouteCollection $routes)
    {
        $this->routes = $routes;
    }

    /**
     * @inheritdoc
     */
    public function match(
        string $httpMethod,
        string $host,
        string $path,
        array $headers = []
    ) : MatchedRoute {
        $hostAndPath = $host . $path;
        $routesByMethod = $this->routes->getByMethod(strtoupper($httpMethod));

        foreach (array_chunk($routesByMethod, self::ROUTE_CHUNK_SIZE, true) as $chunkedRoutes) {
            $routesByCapturingGroupOffsets = [];
            $regex = $this->buildRegex($chunkedRoutes, $routesByCapturingGroupOffsets);
            $matches = [];

            if (preg_match($regex, $hostAndPath, $matches) !== 1) {
                continue;
            }

            // Remove the subject of the matches
            array_shift($matches);

            foreach ($routesByCapturingGroupOffsets as $offset => $route) {
                if ($matches[$offset] === '') {
                    continue;
                }

                if (!$this->headersMatch($route->getHeadersToMatch(), $headers)) {
                    continue;
                }
                // Since the first value in this route's capturing group is the entire matched route,
                // start with the next offset, which will contain the route variables
                $routeVarNamesToValues = [];
                $uriTemplate = $route->getUriTemplate();
                $routeVarValues = array_slice($matches, $offset + 1, count($uriTemplate->getRouteVarNames()));
                
                if (!$this->routeVarsMatch($uriTemplate, $routeVarValues, $routeVarNamesToValues)) {
                    continue;
                }

                return new MatchedRoute(
                    $route->getAction(),
                    $routeVarNamesToValues,
                    $route->getMiddlewareBindings()
                );
            }
        }

        throw new RouteNotFoundException();
    }
    
    /**
     * Builds a regex from a list of routes
     * 
     * @param Route[] $routes The list of routes whose regexes we're building from 
     * @param Route[] $routesByCapturingGroupOffsets The mapping of capturing group offsets to routes that we'll build
     * @return string The built regex
     */
    private function buildRegex(array $routes, array &$routesByCapturingGroupOffsets) : string
    {
        $routesByCapturingGroupOffsets = [];
        $capturingGroupOffset = 0;
        $regexes = [];

        foreach ($routes as $route) {
            $routesByCapturingGroupOffsets[$capturingGroupOffset] = $route;
            $uriTemplate = $route->getUriTemplate();
            // Each regex has a capturing group around the entire thing, hence the + 1
            $capturingGroupOffset += count($uriTemplate->getRouteVarNames()) + 1;
            $regexes[] = $uriTemplate->getRegex();
        }
        
        return '#^(?:(' . implode(')|(', $regexes) . '))$#';
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
     * @param array $routeVarValues The matches from the regex
     * @param array $defaultRouteVars The mapping of variable names to their default values
     */
    private function populateRouteVars(
        array &$routeVars,
        array $routeVarNames,
        array $routeVarValues,
        array $defaultRouteVars
    ) : void {
        $routeVars = [];

        // Set any missing route vars to their default values, if they have any
        foreach ($defaultRouteVars as $name => $defaultValue) {
            $routeVarIndex = array_search($name, $routeVarNames);

            if (!isset($routeVarValues[$routeVarIndex])) {
                $routeVarValues[$routeVarIndex] = $defaultValue;
            }
        }

        foreach ($routeVarValues as $matchIndex => $value) {
            $routeVars[$routeVarNames[$matchIndex]] = $value;
        }
    }
    
    /**
     * Checks whether or not the route vars match the URI template
     * 
     * @param UriTemplate $uriTemplate The URI template to match against
     * @param array $routeVarValues The list of route var values
     * @param array $routeVarNamesToValues The mapping of route var names to their values
     * @return bool True if the route vars match, otherwise false
     */
    private function routeVarsMatch(UriTemplate $uriTemplate, array $routeVarValues, array &$routeVarNamesToValues) : bool
    {
        $this->populateRouteVars(
            $routeVarNamesToValues,
            $uriTemplate->getRouteVarNames(),
            $routeVarValues,
            $uriTemplate->getDefaultRouteVars()
        );
        
        foreach ($uriTemplate->getRouteVarRules() as $name => $rules) {
            foreach ($rules as $rule) {
                if (isset($routeVarNamesToValues[$name]) && !$rule->passes($routeVarNamesToValues[$name])) {
                    return false;
                }
            }
        }

        return true;
    }
}
