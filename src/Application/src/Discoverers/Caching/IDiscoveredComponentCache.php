<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Discoverers\Caching;

use Aphiria\Application\Discoverers\DiscoveredComponent;
use Aphiria\Application\Discoverers\IComponentDiscoverer;
use RuntimeException;

/**
 * Defines the interface for discovered component caches to implement
 */
interface IDiscoveredComponentCache
{
    /**
     * Flushes the cache
     */
    public function flush(): void;

    /**
     * Gets the discovered components from cache for a specific discoverer
     *
     * @param class-string<IComponentDiscoverer> $discovererClassName The discoverer class name to get components for
     * @return list<DiscoveredComponent>|null The discovered components if they existed in cache, otherwise null
     * @throws RuntimeException Thrown if the cached components aren't the expected type
     */
    public function get(string $discovererClassName): ?array;

    /**
     * Gets whether or not discovered components are cached for a specific discoverer
     *
     * @param class-string<IComponentDiscoverer> $discovererClassName The discoverer class name to check
     * @return bool True if the components are cached for this discoverer, otherwise false
     */
    public function has(string $discovererClassName): bool;

    /**
     * Sets the discovered components in cache for a specific discoverer
     *
     * @param class-string<IComponentDiscoverer> $discovererClassName The discoverer class name to cache components for
     * @param list<DiscoveredComponent> $components The discovered components to cache
     */
    public function set(string $discovererClassName, array $components): void;
}
