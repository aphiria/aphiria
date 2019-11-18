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

use Aphiria\Routing\AggregateRouteRegistrant;
use Aphiria\Routing\IRouteRegistrant;
use Aphiria\Routing\RouteCollection;

/**
 * Defines a route registrant that is backed by a cache
 */
final class CachedRouteRegistrant extends AggregateRouteRegistrant
{
    /** @var IRouteCache The optional route cache to store compiled routes in */
    private IRouteCache $routeCache;

    /**
     * @inheritdoc
     * @param IRouteCache $routeCache The route cache to use
     */
    public function __construct(IRouteCache $routeCache, IRouteRegistrant $initialRouteRegistrant = null)
    {
        parent::__construct($initialRouteRegistrant);

        $this->routeCache = $routeCache;
    }

    /**
     * @inheritdoc
     */
    public function registerRoutes(RouteCollection $routes): void
    {
        if (($cachedRoutes = $this->routeCache->get()) !== null) {
            $routes->addMany($cachedRoutes->getAll());

            return;
        }

        parent::registerRoutes($routes);

        // Save this to cache for next time
        $this->routeCache->set($routes);
    }
}
