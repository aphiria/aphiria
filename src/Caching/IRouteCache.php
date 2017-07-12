<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Caching;

use Opulence\Routing\Matchers\RouteCollection;

/**
 * Defines the interface for route builder caches to implement
 */
interface IRouteCache
{
    /**
     * Flushes the route cache
     */
    public function flush() : void;

    /**
     * Gets the route collection from cache
     *
     * @return RouteCollection|null The route collection if it existed in cache, otherwise null
     */
    public function get() : ?RouteCollection;

    /**
     * Gets whether or not the route collection is cached
     *
     * @return bool True if the route collection is cached, otherwise false
     */
    public function has() : bool;

    /**
     * Sets the route collection in cache
     *
     * @param RouteCollection $routes The route collection to cache
     */
    public function set(RouteCollection $routes) : void;
}
