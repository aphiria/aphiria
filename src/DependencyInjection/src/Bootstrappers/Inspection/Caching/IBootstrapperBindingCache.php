<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\DependencyInjection\Bootstrappers\Inspection\Caching;

use Aphiria\DependencyInjection\Bootstrappers\Inspection\BootstrapperBinding;

/**
 * Defines the cache for bootstrapper bindings
 */
interface IBootstrapperBindingCache
{
    /**
     * Flushes the cache
     */
    public function flush(): void;

    /**
     * Gets the bootstrapper bindings from cache if they exist
     *
     * @return BootstrapperBinding[]|null The bootstrapper bindings if they were found, otherwise null
     */
    public function get(): ?array;

    /**
     * Writes the bootstrapper bindings
     *
     * @param BootstrapperBinding[] $bindings The bindings to write
     */
    public function set(array $bindings): void;
}
