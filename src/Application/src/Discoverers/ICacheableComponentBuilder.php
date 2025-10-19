<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Discoverers;

/**
 * Defines the interface for cacehable component builders to implement
 */
interface ICacheableComponentBuilder extends IComponentBuilder
{
    /**
     * Builds the component from cache
     */
    public function buildFromCache(): void;

    /**
     * Flushes the cache
     */
    public function flush(): void;

    /**
     * Whether or not the cache has a value
     *
     * @return bool True if the cache has a value, otherwise false
     */
    public function has(): bool;
}
