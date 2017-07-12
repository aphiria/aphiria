<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers;

/**
 * Defines a list of routes that can be used by a router
 */
class RouteCollection
{
    /** @var array The list of methods to their various routes */
    private $routes = [
        'DELETE' => [],
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'HEAD' => [],
        'OPTIONS' => [],
        'PATCH' => []
    ];
    /** @var Route[] The mapping of route names to routes */
    private $namedRoutes = [];

    /**
     * Performs a deep clone of the routes
     */
    public function __clone()
    {
        foreach ($this->routes as $method => $routesByMethod) {
            foreach ($routesByMethod as $index => $route) {
                $this->routes[$method][$index] = clone $route;
            }
        }

        foreach ($this->namedRoutes as $name => $route) {
            $this->namedRoutes[$name] = clone $route;
        }
    }

    /**
     * Adds a route to the collection
     *
     * @param Route $route The route to add
     */
    public function add(Route $route)
    {
        foreach ($route->getHttpMethods() as $method) {
            $this->routes[$method][] = $route;

            if ($route->getName() !== null) {
                $this->namedRoutes[$route->getName()] =& $route;
            }
        }
    }

    /**
     * Adds a list of routes to the collection
     *
     * @param Route[] $routes The routes to add
     */
    public function addMany(array $routes)
    {
        // I'm purposely copying the code from add() to reduce method calls with many routes
        foreach ($routes as $route) {
            foreach ($route->getHttpMethods() as $method) {
                $this->routes[$method][] = $route;

                if ($route->getName() !== null) {
                    $this->namedRoutes[$route->getName()] =& $route;
                }
            }
        }
    }

    /**
     * Gets all the routes
     *
     * @return Route[] The list of routes
     */
    public function getAll() : array
    {
        return $this->routes;
    }

    /**
     * Gets all the routes for a particular HTTP method
     *
     * @param string The HTTP method whose routes we want
     * @return Route[] The list of routes
     */
    public function getByMethod(string $method) : array
    {
        return $this->routes[$method] ?? [];
    }

    /**
     * Gets the route with the input name
     *
     * @param string $name The name to search for
     * @return Route|null The route with the input name if one existed, otherwise null
     */
    public function getNamedRoute(string $name) : ?Route
    {
        if (isset($this->namedRoutes[$name])) {
            return $this->namedRoutes[$name];
        }

        return null;
    }
}
