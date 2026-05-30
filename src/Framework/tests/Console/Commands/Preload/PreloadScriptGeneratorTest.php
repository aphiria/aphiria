<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2026 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Commands\Preload;

use Aphiria\Framework\Console\Commands\Preload\PreloadException;
use Aphiria\Framework\Console\Commands\Preload\PreloadScriptGenerator;
use PHPUnit\Framework\TestCase;

class PreloadScriptGeneratorTest extends TestCase
{
    private PreloadScriptGenerator $generator;
    private string $tempDir;

    protected function setUp(): void
    {
        $this->generator = new PreloadScriptGenerator();
        $this->tempDir = \sys_get_temp_dir() . '/preload_test_' . \uniqid();
        \mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        // Clean up temp files
        $files = \glob($this->tempDir . '/*');

        if ($files !== false) {
            foreach ($files as $file) {
                \unlink($file);
            }
        }

        \rmdir($this->tempDir);
    }

    public function testGenerateCreatesPreloadScript(): void
    {
        $files = ['/path/to/file1.php', '/path/to/file2.php'];
        $outputPath = $this->tempDir . '/preload.php';

        $this->generator->generate($files, $outputPath);

        $this->assertFileExists($outputPath);
        $content = \file_get_contents($outputPath);
        $this->assertStringContainsString('<?php', $content);
        $this->assertStringContainsString('Aphiria Preload Script', $content);
        $this->assertStringContainsString('/path/to/file1.php', $content);
        $this->assertStringContainsString('/path/to/file2.php', $content);
        $this->assertStringContainsString('opcache_compile_file', $content);
    }

    public function testGenerateCreatesValidPhpSyntax(): void
    {
        $files = ['/path/to/file.php'];
        $outputPath = $this->tempDir . '/preload.php';

        $this->generator->generate($files, $outputPath);

        //  has valid PHP syntax?
        $output = [];
        $returnCode = 0;
        \exec('php -l ' . \escapeshellarg($outputPath) . ' 2>&1', $output, $returnCode);
        $this->assertSame(0, $returnCode, 'Generated PHP file has syntax errors: ' . \implode("\n", $output));
    }

    public function testGenerateEscapesFilePaths(): void
    {
        $files = ["/path/to/file's.php"];
        $outputPath = $this->tempDir . '/preload.php';

        $this->generator->generate($files, $outputPath);

        $content = \file_get_contents($outputPath);
        // Check that the single quote is escaped
        $this->assertStringContainsString("\\'", $content);
    }

    public function testGenerateIncludesFileCount(): void
    {
        $files = ['/path/to/file1.php', '/path/to/file2.php', '/path/to/file3.php'];
        $outputPath = $this->tempDir . '/preload.php';

        $this->generator->generate($files, $outputPath);

        $content = \file_get_contents($outputPath);
        $this->assertStringContainsString('Files: 3', $content);
    }

    public function testGenerateIncludesFileExistsCheck(): void
    {
        $files = ['/path/to/file.php'];
        $outputPath = $this->tempDir . '/preload.php';

        $this->generator->generate($files, $outputPath);

        $content = \file_get_contents($outputPath);
        $this->assertStringContainsString('file_exists', $content);
    }

    public function testGenerateIncludesPhpIniInstructions(): void
    {
        $files = ['/path/to/file.php'];
        $outputPath = $this->tempDir . '/preload.php';

        $this->generator->generate($files, $outputPath);

        $content = \file_get_contents($outputPath);
        $this->assertStringContainsString('opcache.preload=', $content);
        $this->assertStringContainsString('opcache.preload_user=www-data', $content);
    }

    public function testGenerateThrowsExceptionWhenCannotWriteFile(): void
    {
        $this->expectException(PreloadException::class);
        $this->expectExceptionMessage('Failed to write preload script');

        $files = ['/path/to/file.php'];
        // Use a path that doesn't exist
        $outputPath = '/nonexistent/directory/preload.php';

        $this->generator->generate($files, $outputPath);
    }

    public function testGenerateThrowsExceptionWithEmptyFiles(): void
    {
        $this->expectException(PreloadException::class);
        $this->expectExceptionMessage('No files to preload');

        $this->generator->generate([], $this->tempDir . '/preload.php');
    }
}
