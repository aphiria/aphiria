<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

use Aphiria\Routing\Caching\IRouteCache;

/**
 * Defines a collection of route registrants that can be run in serial
 */
class RouteRegistrantCollection implements IRouteRegistrant
{
    /** @var IRouteRegistrant[] The list of route registrants */
    protected array $routeRegistrants = [];
    /** @var IRouteCache|null The optional route cache */
    private ?IRouteCache $routeCache;

    /**
     * @param IRouteCache|null $routeCache The optional route cache
     */
    public function __construct(IRouteCache $routeCache = null)
    {
        $this->routeCache = $routeCache;
    }

    /**
     * Adds a route registrant to the collection
     *
     * @param IRouteRegistrant $routeRegistrant The registrant to add
     */
    public function add(IRouteRegistrant $routeRegistrant): void
    {
        $this->routeRegistrants[] = $routeRegistrant;
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteCollection $routes): void
    {
        if ($this->routeCache !== null && ($cachedRoutes = $this->routeCache->get()) !== null) {
            $routes->copy($cachedRoutes);

            return;
        }

        foreach ($this->routeRegistrants as $routeRegistrant) {
            $routeRegistrant->registerRoutes($routes);
        }

        if ($this->routeCache !== null) {
            $this->routeCache->set($routes);
        }
    }
}
