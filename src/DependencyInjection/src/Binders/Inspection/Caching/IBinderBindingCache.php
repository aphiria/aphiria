<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Inspection\Caching;

use Aphiria\DependencyInjection\Binders\Inspection\BinderBinding;

/**
 * Defines the cache for binder bindings
 */
interface IBinderBindingCache
{
    /**
     * Flushes the cache
     */
    public function flush(): void;

    /**
     * Gets the binder bindings from cache if they exist
     *
     * @return BinderBinding[]|null The binder bindings if they were found, otherwise null
     */
    public function get(): ?array;

    /**
     * Writes the binder bindings
     *
     * @param BinderBinding[] $bindings The bindings to write
     */
    public function set(array $bindings): void;
}
