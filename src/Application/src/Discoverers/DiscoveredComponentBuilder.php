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
        /** @var list<IComponentDiscoverer> $discoverers */
        $discoverers = [];
        /** @var array<class-string<IComponentDiscoverer>, IComponentDiscoverer> $discovererClassNamesToBuilders */
        $discovererClassNamesToBuilders = [];

        /**
         * TODO:
         * - I should really look into how I can use the actual components (eg RouteComponent) to do things like register routes, which creates more consistency in how things get registered with Aphiria - right now, there are too many ways of doing things.
         *      - How do I pass the components to the component builders?  I don't believe they're ever bound to the container, which means they'd be separate instances
         *          - Where are these actually getting instantiated and bound to the container so that IApplicationBuilder can get the same instance (calling resolve() will return a new instance every time unless we bind it as an instance)
         *          - I could pass IApplicationBuilder into the IComponentBuilder::__construct(), but then it will acts as a service locator to grab the component I need, whereas what I really need is a place to bind the components instance to the container and inject that (all without using Binders)
         *              - Another problem with this if a 3rd party component doesn't bind components to the container prior to returning them from IApplicationBuilder()::getComponent(), a new instance will be resolved each time by the container
         *          - The crux of my issues is that I need services that are auto-wired as constructor parameters to be bound instances, themselves, and the container does not support that
         * - Likely need BuilderDiscoverer to be cacheable so I can bypass all this if there is a cache
         * - For now, just proceeding to write the code as if I don't have any caching
         */
        // Find all discoverers
        foreach ($this->scanDirectory($this->path, $discovererDiscoverer) as $discoveredDiscovererComponent) {
            \assert($discoveredDiscovererComponent->class->implementsInterface(IComponentDiscoverer::class));
            $discoverers[] = $this->resolver->resolve($discoveredDiscovererComponent->class->name);
        }

        // Find all builders
        foreach ($this->scanDirectory($this->path, $builderDiscoverer) as $discoveredBuilderComponent) {
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

            $discovererClassNamesToBuilders[$discoverer::class]->build($this->scanDirectory($this->path, $discoverer));
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
