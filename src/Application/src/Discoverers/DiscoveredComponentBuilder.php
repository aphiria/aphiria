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

use Aphiria\Application\Discoverers\Caching\IDiscoveredComponentCache;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use RegexIterator;
use RuntimeException;

/**
 * Defines the discovered component builder
 */
final class DiscoveredComponentBuilder
{
    /**
     * @param string $path The path to scan for discoverers in
     * @param IServiceResolver $resolver The resolver to use
     * @param IDiscoveredComponentCache|null $cache The optional cache to use for discovered components
     */
    public function __construct(
        private readonly string $path,
        private readonly IServiceResolver $resolver,
        private readonly ?IDiscoveredComponentCache $cache = null,
    ) {}

    /**
     * Builds all discovered components
     */
    public function build(): void
    {
        /** @var list<IComponentDiscoverer> $discoverers */
        $discoverers = [];
        /** @var array<class-string<IComponentDiscoverer>, IComponentBuilder> $discovererClassNamesToBuilders */
        $discovererClassNamesToBuilders = [];

        // Find all discoverers
        $discovererDiscoverer = new ComponentDiscovererDiscoverer();

        foreach ($this->getDiscoveredComponents($discovererDiscoverer) as $discoveredDiscovererComponent) {
            \assert($discoveredDiscovererComponent->class->implementsInterface(IComponentDiscoverer::class));
            $discoverers[] = $this->resolver->resolve($discoveredDiscovererComponent->class->name);
        }

        // Find all builders
        $builderDiscoverer = new ComponentBuilderDiscoverer();
        $discoveredBuilderComponents = $this->getDiscoveredComponents($builderDiscoverer);

        foreach ($discoveredBuilderComponents as $discoveredBuilderComponent) {
            \assert($discoveredBuilderComponent->class->implementsInterface(IComponentBuilder::class));
            $builder = $this->resolver->resolve($discoveredBuilderComponent->class->name);

            if (isset($discovererClassNamesToBuilders[$builder->discovererClassName])) {
                throw new RuntimeException("Duplicate builder found for discoverer $builder->discovererClassName");
            }

            $discovererClassNamesToBuilders[$builder->discovererClassName] = $builder;
        }

        // Build all discovered components
        foreach ($discoverers as $discoverer) {
            if (!isset($discovererClassNamesToBuilders[$discoverer::class])) {
                throw new RuntimeException('No builder found for discoverer ' . $discoverer::class);
            }

            $discoveredComponents = $this->getDiscoveredComponents($discoverer);
            $discovererClassNamesToBuilders[$discoverer::class]->build($discoveredComponents);
        }
    }

    /**
     * Attempts to get the fully-qualified class name from a PHP file
     *
     * @param string $filePath The path to the PHP file
     * @return string|null The fully-qualified class name if found, otherwise null
     */
    private function getClassNameFromFile(string $filePath): ?string
    {
        $content = \file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        $namespace = null;
        $className = null;

        // Extract namespace
        if (\preg_match('/namespace\s+([^;]+);/i', $content, $matches)) {
            $namespace = $matches[1];
        }

        // Extract class/interface/trait name
        if (\preg_match('/\b(?:class|interface|trait)\s+([a-zA-Z0-9_]+)/i', $content, $matches)) {
            $className = $matches[1];
        }

        if ($namespace === null || $className === null) {
            return null;
        }

        return $namespace . '\\' . $className;
    }

    /**
     * Gets discovered components for a specific discoverer, using cache if available
     *
     * @param IComponentDiscoverer $discoverer The discoverer to get components for
     * @return list<DiscoveredComponent> The list of discovered components
     */
    private function getDiscoveredComponents(IComponentDiscoverer $discoverer): array
    {
        // Try to get from cache first
        if ($this->cache !== null && $this->cache->has($discoverer::class)) {
            return $this->cache->get($discoverer::class);
        }

        // Cache miss - scan directory
        $discoveredComponents = $this->scanDirectory($this->path, $discoverer);

        // Store in cache if cache is configured
        $this->cache?->set($discoverer::class, $discoveredComponents);

        return $discoveredComponents;
    }

    /**
     * Recursively scans a directory for PHP files, reflects classes, and passes them to the discoverer
     *
     * @param string $directory The directory to scan
     * @param IComponentDiscoverer $discoverer The discoverer to use
     * @return list<DiscoveredComponent> The list of discovered components
     */
    private function scanDirectory(string $directory, IComponentDiscoverer $discoverer): array
    {
        $directoryIterator = new RecursiveDirectoryIterator($directory);
        $recursiveIterator = new RecursiveIteratorIterator($directoryIterator);

        // Filter for PHP files
        $phpFiles = new RegexIterator($recursiveIterator, '/^.+\.php$/i');
        $discoveredComponents = [];

        foreach ($phpFiles as $phpFile) {
            $className = $this->getClassNameFromFile($phpFile->getRealPath());

            if ($className === null) {
                continue;
            }

            try {
                $discoveredComponents = [...$discoveredComponents, ...$discoverer->discover(new ReflectionClass($className))];
            } catch (ReflectionException) {
                // Skip classes that cannot be reflected
                continue;
            }
        }

        return $discoveredComponents;
    }
}
