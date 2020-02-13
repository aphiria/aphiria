<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Caching;

use Aphiria\Console\Commands\CommandRegistry;

/**
 * Defines the interface for command registry caches to implement
 */
interface ICommandRegistryCache
{
    /**
     * Flushes the cache
     */
    public function flush(): void;

    /**
     * Gets the commands from cache
     *
     * @return CommandRegistry|null The commands if they existed in cache, otherwise null
     */
    public function get(): ?CommandRegistry;

    /**
     * Gets whether or not the commands are cached
     *
     * @return bool True if the commands are cached, otherwise false
     */
    public function has(): bool;

    /**
     * Sets the commands in cache
     *
     * @param CommandRegistry $commands The commands to cache
     */
    public function set(CommandRegistry $commands): void;
}
