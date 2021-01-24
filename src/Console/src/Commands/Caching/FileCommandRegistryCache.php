<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Caching;

use Aphiria\Console\Commands\CommandRegistry;
use RuntimeException;

/**
 *
 */
final class FileCommandRegistryCache implements ICommandRegistryCache
{
    /**
     * @param string $path The path to the cache file
     */
    public function __construct(private string $path)
    {
    }

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        if ($this->has()) {
            @\unlink($this->path);
        }
    }

    /**
     * @inheritoc
     */
    public function get(): ?CommandRegistry
    {
        if (!\file_exists($this->path)) {
            return null;
        }

        $commands = \unserialize(\file_get_contents($this->path));

        if ($commands !== null && !$commands instanceof CommandRegistry) {
            throw new RuntimeException('Commands must be instance of ' . CommandRegistry::class . ' or null');
        }

        return $commands;
    }

    /**
     * @inheritoc
     */
    public function has(): bool
    {
        return \file_exists($this->path);
    }

    /**
     * @inheritdoc
     */
    public function set(CommandRegistry $commands): void
    {
        \file_put_contents($this->path, \serialize($commands));
    }
}
