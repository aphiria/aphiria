<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Caching;

use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;

/**
 * Defines the interface for constraint registry caches to implement
 */
interface IObjectConstraintRegistryCache
{
    /**
     * Flushes the cache
     */
    public function flush(): void;

    /**
     * Gets the constraints from cache
     *
     * @return ObjectConstraintsRegistry|null The constraints if they existed in cache, otherwise null
     */
    public function get(): ?ObjectConstraintsRegistry;

    /**
     * Gets whether or not the constraints are cached
     *
     * @return bool True if the constraints are cached, otherwise false
     */
    public function has(): bool;

    /**
     * Sets the constraints in cache
     *
     * @param ObjectConstraintsRegistry $objectConstraints The constraints to cache
     */
    public function set(ObjectConstraintsRegistry $objectConstraints): void;
}
