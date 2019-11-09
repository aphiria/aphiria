<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

use Aphiria\Routing\Caching\IRouteCache;
use Closure;

/**
 * Defines a route factory that lazily creates routes when necessary
 */
final class LazyRouteFactory implements IRouteFactory
{
    /** @var Closure[] The list of factories that will actually create the routes */
    private array $routeRegistrant = [];
    /** @var IRouteCache|null The optional route cache to store compiled routes in */
    private ?IRouteCache $routeCache;

    /**
     * @param Closure|null $routeRegistrant The initial registrant that will be used to register routes
     *      Note: Must take in a RouteCollection and be void
     * @param IRouteCache|null $routeCache The route cache, if we're using a cache, otherwise null
     */
    public function __construct(Closure $routeRegistrant = null, IRouteCache $routeCache = null)
    {
        if ($routeRegistrant !== null) {
            $this->routeRegistrant[] = $routeRegistrant;
        }

        $this->routeCache = $routeCache;
    }

    /**
     * Adds a route registrant
     *
     * @param Closure $routeRegistrant The registrant to add
     *      Note: Must take in a RouteCollection and be void
     */
    public function addRouteRegistrant(Closure $routeRegistrant): void
    {
        $this->routeRegistrant[] = $routeRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function createRoutes(): RouteCollection
    {
        if ($this->routeCache !== null && ($routes = $this->routeCache->get()) !== null) {
            return $routes;
        }

        $routes = new RouteCollection();

        foreach ($this->routeRegistrant as $routeFactory) {
            $routeFactory($routes);
        }

        // Save this to cache for next time
        if ($this->routeCache !== null) {
            $this->routeCache->set($routes);
        }

        return $routes;
    }
}
