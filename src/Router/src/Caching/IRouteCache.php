<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Caching;

use Aphiria\Routing\RouteCollection;
use RuntimeException;

/**
 * Defines the interface for route caches to implement
 */
interface IRouteCache
{
    /**
     * Flushes the cache
     */
    public function flush(): void;

    /**
     * Gets the routes from cache
     *
     * @return RouteCollection|null The routes if they existed in cache, otherwise null
     * @throws RuntimeException Thrown if the cached routes aren't the expected type
     */
    public function get(): ?RouteCollection;

    /**
     * Gets whether or not the routes are cached
     *
     * @return bool True if the routes are cached, otherwise false
     */
    public function has(): bool;

    /**
     * Sets the routes in cache
     *
     * @param RouteCollection $routes The routes to cache
     */
    public function set(RouteCollection $routes): void;
}
