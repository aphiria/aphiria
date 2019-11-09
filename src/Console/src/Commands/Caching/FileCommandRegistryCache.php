<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Commands\Caching;

use Aphiria\Console\Commands\CommandRegistry;

/**
 *
 */
final class FileCommandRegistryCache implements ICommandRegistryCache
{
    /** @var string The path to the cache file */
    private string $path;

    /**
     * @param string $path The path to the cache file
     */
    public function __construct(string $path)
    {
        $this->path = $path;
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

        return \unserialize(\file_get_contents($this->path));
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
