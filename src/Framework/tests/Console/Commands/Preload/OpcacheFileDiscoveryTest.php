<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Commands\Preload;

use Aphiria\Framework\Console\Commands\Preload\OpcacheFileDiscovery;
use Aphiria\Framework\Console\Commands\Preload\PreloadException;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

class OpcacheFileDiscoveryTest extends TestCase
{
    private OpcacheFileDiscovery $discovery;

    protected function setUp(): void
    {
        $this->discovery = new OpcacheFileDiscovery();
    }

    #[RequiresPhpExtension('Zend OPcache')]
    public function testDiscoverFilesReturnsFilesFromOpcache(): void
    {
        // This test requires OPcache to be enabled
        // It serves as a basic smoke test when OPcache is available
        if (!\function_exists('opcache_get_status')) {
            $this->markTestSkipped('OPcache extension is not available');
        }

        $status = \opcache_get_status(true);

        if ($status === false) {
            $this->markTestSkipped('OPcache is disabled');
        }

        // Just verify no exception is thrown and an array is returned
        $files = $this->discovery->discoverFiles();
        $this->assertIsArray($files);
    }

    #[RequiresPhpExtension('Zend OPcache')]
    public function testDiscoverFilesFiltersTestFiles(): void
    {
        if (!\function_exists('opcache_get_status')) {
            $this->markTestSkipped('OPcache extension is not available');
        }

        $status = \opcache_get_status(true);

        if ($status === false) {
            $this->markTestSkipped('OPcache is disabled');
        }

        $files = $this->discovery->discoverFiles();

        // Verify no test files are included
        foreach ($files as $file) {
            $this->assertStringNotContainsString('/tests/', $file);
            $this->assertStringNotContainsString('/Tests/', $file);
            $this->assertStringNotContainsString('Test.php', $file);
            $this->assertStringNotContainsString('/vendor/phpunit/', $file);
        }
    }

    #[RequiresPhpExtension('Zend OPcache')]
    public function testDiscoverFilesAppliesCustomExcludePatterns(): void
    {
        if (!\function_exists('opcache_get_status')) {
            $this->markTestSkipped('OPcache extension is not available');
        }

        $status = \opcache_get_status(true);

        if ($status === false) {
            $this->markTestSkipped('OPcache is disabled');
        }

        $excludePatterns = ['/vendor/'];
        $files = $this->discovery->discoverFiles($excludePatterns);

        // Verify excluded patterns are not included
        foreach ($files as $file) {
            $this->assertStringNotContainsString('/vendor/', $file);
        }
    }

    #[RequiresPhpExtension('Zend OPcache')]
    public function testDiscoverFilesReturnsResultsSortedAlphabetically(): void
    {
        if (!\function_exists('opcache_get_status')) {
            $this->markTestSkipped('OPcache extension is not available');
        }

        $status = \opcache_get_status(true);

        if ($status === false) {
            $this->markTestSkipped('OPcache is disabled');
        }

        $files = $this->discovery->discoverFiles();

        if (\count($files) > 1) {
            $sortedFiles = $files;
            \sort($sortedFiles);
            $this->assertSame($sortedFiles, $files);
        }
    }
}
