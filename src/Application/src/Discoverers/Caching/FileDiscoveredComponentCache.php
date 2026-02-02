<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Discoverers\Caching;

use Aphiria\Application\Discoverers\DiscoveredComponent;
use Aphiria\Application\Discoverers\IComponentDiscoverer;
use RuntimeException;

/**
 * Defines the discovered component cache that uses local files
 */
final class FileDiscoveredComponentCache implements IDiscoveredComponentCache
{
    /**
     * @param string $directory The directory to store cache files in
     */
    public function __construct(private readonly string $directory) {}

    /**
     * @inheritdoc
     */
    public function flush(): void
    {
        if (!\is_dir($this->directory)) {
            return;
        }

        foreach (\glob($this->directory . '/*.cache') as $cacheFile) {
            @\unlink($cacheFile);
        }
    }

    /**
     * @inheritdoc
     */
    public function get(string $discovererClassName): ?array
    {
        $path = $this->getPath($discovererClassName);

        if (!\file_exists($path)) {
            return null;
        }

        $components = \unserialize(\file_get_contents($path));

        if (!\is_array($components)) {
            throw new RuntimeException('Cached discovered components must be an array');
        }

        foreach ($components as $component) {
            if (!$component instanceof DiscoveredComponent) {
                throw new RuntimeException('Cached discovered components must be instances of ' . DiscoveredComponent::class);
            }
        }

        return $components;
    }

    /**
     * @inheritdoc
     */
    public function has(string $discovererClassName): bool
    {
        return \file_exists($this->getPath($discovererClassName));
    }

    /**
     * @inheritdoc
     */
    public function set(string $discovererClassName, array $components): void
    {
        $path = $this->getPath($discovererClassName);
        $directory = \dirname($path);

        if (!\is_dir($directory)) {
            \mkdir($directory, 0755, true);
        }

        \file_put_contents($path, \serialize($components));
    }

    /**
     * Gets the cache file path for a specific discoverer
     *
     * @param class-string<IComponentDiscoverer> $discovererClassName The discoverer class name
     * @return string The cache file path
     */
    private function getPath(string $discovererClassName): string
    {
        return $this->directory . '/' . \str_replace('\\', '_', $discovererClassName) . '.cache';
    }
}
