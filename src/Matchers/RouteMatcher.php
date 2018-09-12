<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers;

use Opulence\Routing\Matchers\Constraints\IRouteConstraint;
use Opulence\Routing\Regexes\GroupRegexCollection;
use Opulence\Routing\UriTemplates\UriTemplate;

/**
 * Defines a route matcher
 */
class RouteMatcher implements IRouteMatcher
{
    /** @var GroupRegexCollection The list of regexes to match against */
    private $regexes;
    /** @var IRouteConstraint[] The list of custom route constraints to apply */
    private $routeConstraints;

    /**
     * @param GroupRegexCollection $regexes The list of regexes to match against
     * @param IRouteConstraint[] $routeConstraints The list of custom route constraints to apply
     */
    public function __construct(GroupRegexCollection $regexes, array $routeConstraints = [])
    {
        $this->regexes = $regexes;
        $this->routeConstraints = $routeConstraints;
    }

    /**
     * @inheritdoc
     */
    public function match(string $httpMethod, string $host, string $path, array $headers = []): MatchedRoute
    {
        $hostAndPath = $host . $path;
        $regexesByMethod = $this->regexes->getByMethod(strtoupper($httpMethod));

        foreach ($regexesByMethod as $regex) {
            $matches = [];

            if (preg_match($regex->getGroupRegex(), $hostAndPath, $matches, PREG_UNMATCHED_AS_NULL) !== 1) {
                continue;
            }

            foreach ($regex->getRoutesByCapturingGroupOffsets() as $offset => $route) {
                // The first values in the matches is the subject, so skip that one
                if ($matches[$offset + 1] === null) {
                    continue;
                }

                // Since the first value in this route's capturing group is the entire matched route and the first
                // value in matches is the subject, start with the next offset, which will contain the route variables
                $routeVarNamesToValues = [];
                $uriTemplate = $route->getUriTemplate();
                $routeVarValues = \array_slice($matches, $offset + 2, \count($uriTemplate->getRouteVarNames()));
                $this->populateRouteVars(
                    $routeVarNamesToValues,
                    $uriTemplate->getRouteVarNames(),
                    $routeVarValues,
                    $uriTemplate->getDefaultRouteVars()
                );

                if (!$this->routeVarsMatch($uriTemplate, $routeVarNamesToValues)) {
                    continue;
                }

                foreach ($this->routeConstraints as $routeConstraint) {
                    if (!$routeConstraint->isMatch($host, $path, $headers, $route)) {
                        continue 2;
                    }
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
    ): void {
        $routeVars = [];

        // Set any missing route vars to their default values, if they have any
        foreach ($defaultRouteVars as $name => $defaultValue) {
            $routeVarIndex = array_search($name, $routeVarNames, true);

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
     * @param array $routeVarNamesToValues The mapping of route var names to their values
     * @return bool True if the route vars match, otherwise false
     */
    private function routeVarsMatch(UriTemplate $uriTemplate, array &$routeVarNamesToValues): bool
    {
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
