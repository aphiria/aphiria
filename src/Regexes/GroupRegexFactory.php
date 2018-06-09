<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Regexes;

use Opulence\Routing\Regexes\Caching\IGroupRegexCache;
use Opulence\Routing\Route;
use Opulence\Routing\RouteCollection;

/**
 * Creates group regexes
 */
class GroupRegexFactory
{
    /** @const The number of routes' regexes to combine per group regex */
    private const ROUTE_CHUNK_SIZE = 10;
    /** @var RouteCollection The list of routes to create regexes from */
    private $routes;
    /** @var IGroupRegexCache|null The regex cache if using one, otherwise null */
    private $regexCache;

    /**
     * @param RouteCollection $routes The list of routes to create regexes from
     * @param IGroupRegexCache|null $regexCache The regex cache if using one, otherwise null
     */
    public function __construct(RouteCollection $routes, IGroupRegexCache $regexCache = null)
    {
        $this->routes = $routes;
        $this->regexCache = $regexCache;
    }

    /**
     * Creates group regexes from the list of routes
     *
     * @return GroupRegexCollection The list of group regexes
     */
    public function createRegexes(): GroupRegexCollection
    {
        if ($this->regexCache !== null && ($regexes = $this->regexCache->get()) !== null) {
            return $regexes;
        }

        $regexes = new GroupRegexCollection();

        foreach ($this->routes->getAll() as $httpMethod => $routesByMethod) {
            foreach (array_chunk($routesByMethod, self::ROUTE_CHUNK_SIZE, true) as $chunkedRoutes) {
                $routesByCapturingGroupOffsets = [];
                $regex = $this->buildRegex($chunkedRoutes, $routesByCapturingGroupOffsets);
                $regexes->add($httpMethod, new GroupRegex($regex, $routesByCapturingGroupOffsets));
            }
        }

        if ($this->regexCache !== null) {
            $this->regexCache->set($regexes);
        }

        return $regexes;
    }

    /**
     * Builds a regex from a list of routes
     *
     * @param Route[] $routes The list of routes whose regexes we're building from
     * @param Route[] $routesByCapturingGroupOffsets The mapping of capturing group offsets to routes that we'll build
     * @return string The built regex
     */
    private function buildRegex(array $routes, array &$routesByCapturingGroupOffsets): string
    {
        $routesByCapturingGroupOffsets = [];
        $capturingGroupOffset = 0;
        $regexes = '';

        foreach ($routes as $route) {
            $routesByCapturingGroupOffsets[$capturingGroupOffset] = $route;
            $uriTemplate = $route->getUriTemplate();
            $regexes .= '(' . $uriTemplate->getRegex() . ')|';
            // Each regex has a capturing group around the entire thing, hence the + 1
            $capturingGroupOffset += \count($uriTemplate->getRouteVarNames()) + 1;
        }

        return '#^(?:' . rtrim($regexes, '|') . ')$#';
    }
}
