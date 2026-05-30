<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Console\Commands;

use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use Aphiria\Framework\Console\Commands\Preload\IFileDiscovery;
use Aphiria\Framework\Console\Commands\Preload\IPreloadScriptGenerator;
use Aphiria\Framework\Console\Commands\Preload\PreloadException;

/**
 * Defines the console command handler that generates a preload script from OPcache
 */
final class PreloadCommandHandler implements ICommandHandler
{
    /**
     * @param IFileDiscovery $fileDiscovery The file discovery strategy
     * @param IPreloadScriptGenerator $generator The preload script generator
     */
    public function __construct(
        private readonly IFileDiscovery $fileDiscovery,
        private readonly IPreloadScriptGenerator $generator,
    ) {}

    /**
     * @inheritdoc
     */
    #[\Override]
    public function handle(Input $input, IOutput $output): StatusCode
    {
        // Optionally warm URLs first
        /** @var list<string> $urls */
        $urls = $input->arguments['urls'] ?? [];

        foreach ($urls as $url) {
            $output->writeln("<info>Warming: $url</info>");
            @\file_get_contents($url);
        }

        // Get options
        /** @var list<string> $excludePatterns */
        $excludePatterns = $input->options['exclude'] ?? [];
        $minHits = (int) ($input->options['min-hits'] ?? 1);
        $outputPath = (string) ($input->options['output'] ?? 'preload.php');
        $isDryRun = \array_key_exists('dry-run', $input->options);

        try {
            $files = $this->fileDiscovery->discoverFiles($excludePatterns, $minHits);
        } catch (PreloadException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return StatusCode::Error;
        }

        if (empty($files)) {
            $output->writeln('<comment>No files found in OPcache matching the criteria</comment>');

            return StatusCode::Ok;
        }

        $output->writeln('<info>Found ' . \count($files) . ' files in OPcache</info>');

        if ($isDryRun) {
            $output->writeln('');
            $output->writeln('<comment>Files that would be included:</comment>');

            foreach ($files as $file) {
                $output->writeln("  $file");
            }

            return StatusCode::Ok;
        }

        try {
            $this->generator->generate($files, $outputPath);
        } catch (PreloadException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");

            return StatusCode::Error;
        }

        $output->writeln("<success>Generated preload script: $outputPath</success>");
        $output->writeln('');
        $output->writeln('<comment>To use this script, add the following to your php.ini:</comment>');
        $output->writeln("  opcache.preload=$outputPath");
        $output->writeln('  opcache.preload_user=www-data');

        return StatusCode::Ok;
    }
}
