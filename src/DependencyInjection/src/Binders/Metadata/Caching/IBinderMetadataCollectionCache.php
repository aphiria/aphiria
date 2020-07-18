<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata\Caching;

use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollection;

/**
 * Defines the cache for binder metadata collections
 */
interface IBinderMetadataCollectionCache
{
    /**
     * Flushes the cache
     */
    public function flush(): void;

    /**
     * Gets the binder metadata collection from cache if it exists
     *
     * @return BinderMetadataCollection|null The binder metadata collection if it was found, otherwise null
     */
    public function get(): ?BinderMetadataCollection;

    /**
     * Writes the binder metadata collection to cache
     *
     * @param BinderMetadataCollection $binderMetadataCollection The binder metadata collection to write
     */
    public function set(BinderMetadataCollection $binderMetadataCollection): void;
}
