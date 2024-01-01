<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Binders\Metadata\Caching;

use Aphiria\DependencyInjection\Binders\Metadata\BinderMetadataCollection;
use RuntimeException;

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
     * @throws RuntimeException Thrown if the cached collection isn't the expected type
     */
    public function get(): ?BinderMetadataCollection;

    /**
     * Writes the binder metadata collection to cache
     *
     * @param BinderMetadataCollection $binderMetadataCollection The binder metadata collection to write
     */
    public function set(BinderMetadataCollection $binderMetadataCollection): void;
}
