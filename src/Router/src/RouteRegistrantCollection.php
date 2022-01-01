<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing;

use Aphiria\Routing\Caching\IRouteCache;

/**
 * Defines a collection of route registrants that can be run in serial
 */
class RouteRegistrantCollection implements IRouteRegistrant
{
    /** @var list<IRouteRegistrant> The list of route registrants */
    protected array $routeRegistrants = [];

    /**
     * @param IRouteCache|null $routeCache The optional route cache
     */
    public function __construct(private readonly ?IRouteCache $routeCache = null)
    {
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
        if (($cachedRoutes = $this->routeCache?->get()) !== null) {
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
