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

/**
 * Defines a route registrant that is backed by a cache
 */
final class CachedRouteRegistrant extends AggregateRouteRegistrant
{
    /** @var IRouteCache The optional route cache to store compiled routes in */
    private IRouteCache $routeCache;

    /**
     * @param IRouteCache $routeCache The route cache to use
     * @param IRouteRegistrant|null $routeRegistrant The initial registrant that will be used to register routes
     */
    public function __construct(IRouteCache $routeCache, IRouteRegistrant $routeRegistrant = null)
    {
        $this->routeCache = $routeCache;

        if ($routeRegistrant !== null) {
            $this->routeRegistrants[] = $routeRegistrant;
        }
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
