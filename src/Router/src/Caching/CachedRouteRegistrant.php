<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Caching;

use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteRegistrantCollection;

/**
 * Defines a route registrant that is backed by a cache
 */
final class CachedRouteRegistrant implements IRouteRegistrant
{
    /** @var IRouteCache The route cache to store compiled routes in */
    private IRouteCache $routeCache;
    /** @var RouteRegistrantCollection The list of registrants to run on cache miss */
    private RouteRegistrantCollection $routeRegistrants;

    /**
     * @param IRouteCache $routeCache The route cache to use
     * @param RouteRegistrantCollection $routeRegistrants The list of registrants to run on cache miss
     */
    public function __construct(IRouteCache $routeCache, RouteRegistrantCollection $routeRegistrants)
    {
        $this->routeCache = $routeCache;
        $this->routeRegistrants = $routeRegistrants;
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteCollection $routes): void
    {
        if (($cachedRoutes = $this->routeCache->get()) !== null) {
            $routes->copy($cachedRoutes);

            return;
        }

        $this->routeRegistrants->registerRoutes($routes);

        // Save this to cache for next time
        $this->routeCache->set($routes);
    }
}
