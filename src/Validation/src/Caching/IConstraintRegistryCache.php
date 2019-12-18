<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Caching;

use Aphiria\Validation\ConstraintRegistry;

/**
 * Defines the interface for constraint registry caches to implement
 */
interface IConstraintRegistryCache
{
    /**
     * Flushes the cache
     */
    public function flush(): void;

    /**
     * Gets the constraints from cache
     *
     * @return ConstraintRegistry|null The constraints if they existed in cache, otherwise null
     */
    public function get(): ?ConstraintRegistry;

    /**
     * Gets whether or not the constraints are cached
     *
     * @return bool True if the constraints are cached, otherwise false
     */
    public function has(): bool;

    /**
     * Sets the constraints in cache
     *
     * @param ConstraintRegistry $constraints The constraints to cache
     */
    public function set(ConstraintRegistry $constraints): void;
}
