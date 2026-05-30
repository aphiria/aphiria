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
 * Defines the preload script generator
 */
final class PreloadScriptGenerator implements IPreloadScriptGenerator
{
    /**
     * @inheritdoc
     */
    #[\Override]
    public function generate(array $files, string $outputPath): void
    {
        if (empty($files)) {
            throw new PreloadException('No files to preload');
        }

        $date = \date('Y-m-d H:i:s');
        $fileCount = \count($files);

        $script = <<<PHP
<?php

/**
 * Aphiria Preload Script
 *
 * Generated: $date
 * Files: $fileCount
 *
 * To use this script, add the following to your php.ini:
 * opcache.preload=$outputPath
 * opcache.preload_user=www-data
 *
 * @link https://www.php.net/manual/en/opcache.preloading.php
 */

declare(strict_types=1);


PHP;

        foreach ($files as $file) {
            $escapedFile = \addslashes($file);
            $script .= "if (\\file_exists('$escapedFile')) {\n";
            $script .= "    \\opcache_compile_file('$escapedFile');\n";
            $script .= "}\n";
        }

        $result = \file_put_contents($outputPath, $script);

        if ($result === false) {
            throw new PreloadException("Failed to write preload script to '$outputPath'");
        }
    }
}
