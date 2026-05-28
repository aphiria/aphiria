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
 * Defines the interface for preload script generators
 */
interface IPreloadScriptGenerator
{
    /**
     * Generates a preload script containing the specified files
     *
     * @param list<string> $files The file paths to include in the preload script
     * @param string $outputPath The path where the preload script should be written
     * @throws PreloadException Thrown if script generation fails
     */
    public function generate(array $files, string $outputPath): void;
}
