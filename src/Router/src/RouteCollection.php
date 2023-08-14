<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

/**
 * Defines a list of routes that can be used by a router
 */
final class RouteCollection
{
    /** @var array<string, Route> The mapping of route names to routes */
    private array $namedRoutes = [];
    /** @var list<Route> The list of methods to their various routes */
    private array $routes = [];

    /**
     * @param list<Route> $routes The initial list of routes
     */
    public function __construct(array $routes = [])
    {
        $this->addMany($routes);
    }

    /**
     * Adds a route to the collection
     *
     * @param Route $route The route to add
     */
    public function add(Route $route): void
    {
        $this->routes[] = $route;

        if ($route->name !== null) {
            $this->namedRoutes[$route->name] = & $route;
        }
    }

    /**
     * Adds a list of routes to the collection
     *
     * @param list<Route> $routes The routes to add
     */
    public function addMany(array $routes): void
    {
        // Purposely not calling add() so that we save on method calls when adding a lot of routes (micro-optimization)
        foreach ($routes as $route) {
            $this->routes[] = $route;

            if ($route->name !== null) {
                $this->namedRoutes[$route->name] = & $route;
            }
        }
    }

    /**
     * Copies a route collection into this one
     *
     * @param RouteCollection $routes The routes to copy
     * @internal
     */
    public function copy(RouteCollection $routes): void
    {
        $this->routes = $routes->routes;
        $this->namedRoutes = $routes->namedRoutes;
    }

    /**
     * Gets all the routes
     *
     * @return list<Route> The list of routes
     */
    public function getAll(): array
    {
        return $this->routes;
    }

    /**
     * Gets the route with the input name
     *
     * @param string $name The name to search for
     * @return Route|null The route with the input name if one existed, otherwise null
     */
    public function getNamedRoute(string $name): ?Route
    {
        return $this->namedRoutes[$name] ?? null;
    }
}
