<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Commands\Preload;

/**
 * Defines the interface for file discovery strategies used in preload script generation
 */
interface IFileDiscovery
{
    /**
     * Discovers files that should be included in the preload script
     *
     * @param list<string> $excludePatterns Patterns to exclude from results
     * @param int $minHits Minimum number of OPcache hits required to include a file
     * @return list<string> The list of file paths to preload
     * @throws PreloadException Thrown if file discovery fails
     */
    public function discoverFiles(array $excludePatterns = [], int $minHits = 1): array;
}
