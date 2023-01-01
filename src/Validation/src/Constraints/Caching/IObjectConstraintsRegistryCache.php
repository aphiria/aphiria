<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Validation\Constraints\Caching;

use Aphiria\Validation\Constraints\ObjectConstraintsRegistry;
use RuntimeException;

/**
 * Defines the interface for constraint registry caches to implement
 */
interface IObjectConstraintsRegistryCache
{
    /**
     * Flushes the cache
     */
    public function flush(): void;

    /**
     * Gets the constraints from cache
     *
     * @return ObjectConstraintsRegistry|null The constraints if they existed in cache, otherwise null
     * @throws RuntimeException Thrown if the cached constraints aren't the expected type
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
