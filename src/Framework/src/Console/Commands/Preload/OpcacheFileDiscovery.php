<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Commands\Preload;

/**
 * Defines the OPcache-based file discovery strategy
 */
final class OpcacheFileDiscovery implements IFileDiscovery
{
    /**
     * @inheritdoc
     */
    #[\Override]
    public function discoverFiles(array $excludePatterns = [], int $minHits = 1): array
    {
        if (!\function_exists('opcache_get_status')) {
            throw new PreloadException('OPcache extension is not available');
        }

        $status = \opcache_get_status(true);

        if ($status === false) {
            throw new PreloadException('Could not get OPcache status - OPcache may be disabled');
        }

        if (!isset($status['scripts']) || !\is_array($status['scripts'])) {
            throw new PreloadException('No scripts found in OPcache');
        }

        /** @var list<string> $files */
        $files = [];

        /** @var array{hits?: int, full_path?: string} $script */
        foreach ($status['scripts'] as $script) {
            if (!isset($script['hits'], $script['full_path'])) {
                continue;
            }

            if ($script['hits'] < $minHits) {
                continue;
            }

            $files[] = $script['full_path'];
        }

        return $this->filterFiles($files, $excludePatterns);
    }

    /**
     * Filters files based on exclude patterns and removes test files
     *
     * @param list<string> $files The files to filter
     * @param list<string> $excludePatterns The patterns to exclude
     * @return list<string> The filtered files
     */
    private function filterFiles(array $files, array $excludePatterns): array
    {
        // Default exclusions for test files
        $defaultExclusions = [
            '/tests/',
            '/Tests/',
            '/test/',
            '/Test/',
            'Test.php',
            'Tests.php',
            '/vendor/phpunit/',
            '/vendor/bin/',
        ];

        $allExclusions = \array_merge($defaultExclusions, $excludePatterns);
        $filtered = [];

        foreach ($files as $file) {
            $exclude = false;

            foreach ($allExclusions as $pattern) {
                if (\str_contains($file, $pattern)) {
                    $exclude = true;
                    break;
                }
            }

            if (!$exclude) {
                $filtered[] = $file;
            }
        }

        // Sort files for consistent output
        \sort($filtered);

        return $filtered;
    }
}
