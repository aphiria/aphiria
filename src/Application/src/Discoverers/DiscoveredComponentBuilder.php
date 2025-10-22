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

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use RegexIterator;
use RuntimeException;

/**
 * Defines the discovered component builder
 *
 * TODO:  Think about the PHPDoc above, name, and main method of this class - I'm doing more than just scanning - I'm scanning + building
 */
final class DiscoveredComponentBuilder
{
    /**
     * @param string $path The path to scan for discoverers in
     * @param IResolver $resolver The resolver to use
     */
    public function __construct(
        private readonly string $path,
        private readonly IResolver $resolver = new ContainerResolver(),
    ) {}

    /**
     * Builds all discovered components
     */
    public function build(): void
    {
        $discovererDiscoverer = new ComponentDiscovererDiscoverer();
        $builderDiscoverer = new ComponentBuilderDiscoverer();
        $componentNamesToDiscoverers = [];
        $componentNamesToBuilders = [];

        /**
         * TODO:
         * - Likely need BuilderDiscoverer to be cacheable so I can bypass all this if there is a cache
         * - For now, just proceeding to write the code as if I don't have any caching
         */
        // Find all discoverers
        foreach ($this->scanDirectory($this->path, $discovererDiscoverer) as $discoveredComponent) {
            \assert($discoveredComponent->class->implementsInterface(IComponentDiscoverer::class));
            $componentNamesToDiscoverers[$discoveredComponent->componentName] = $this->resolver->resolve($discoveredComponent->class->name);
        }

        // Find all builders
        foreach ($this->scanDirectory($this->path, $builderDiscoverer) as $discoveredComponent) {
            \assert($discoveredComponent->class->implementsInterface(IComponentBuilder::class));
            $componentNamesToBuilders[$discoveredComponent->componentName] = $this->resolver->resolve($discoveredComponent->class->name);
        }

        // Build all discovered components
        foreach ($componentNamesToDiscoverers as $componentName => $discoverer) {
            if (!isset($componentNamesToBuilders[$componentName])) {
                throw new RuntimeException("No builder found for component $componentName");
            }

            $componentNamesToBuilders[$componentName]->build($this->scanDirectory($this->path, $discoverer));
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
