<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Commands;

use Aphiria\Console\Drivers\IDriver;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\StatusCode;
use Aphiria\Framework\Console\Commands\Preload\IFileDiscovery;
use Aphiria\Framework\Console\Commands\Preload\IPreloadScriptGenerator;
use Aphiria\Framework\Console\Commands\Preload\PreloadException;
use Aphiria\Framework\Console\Commands\PreloadCommandHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;

class PreloadCommandHandlerTest extends TestCase
{
    private IFileDiscovery&MockObject $fileDiscovery;
    private IPreloadScriptGenerator&MockObject $generator;
    private IOutput&MockObject $output;
    private PreloadCommandHandler $handler;

    protected function setUp(): void
    {
        $this->fileDiscovery = $this->createMock(IFileDiscovery::class);
        $this->generator = $this->createMock(IPreloadScriptGenerator::class);
        $this->output = $this->createMock(IOutput::class);

        $driver = new class () implements IDriver {
            public int $cliWidth = 80;
            public int $cliHeight = 24;

            public function readHiddenInput(IOutput $output): ?string
            {
                return null;
            }
        };
        $this->output
            ->method(PropertyHook::get('driver'))
            ->willReturn($driver);

        $this->handler = new PreloadCommandHandler($this->fileDiscovery, $this->generator);
    }

    public function testHandleGeneratesPreloadScript(): void
    {
        $files = ['/path/to/file1.php', '/path/to/file2.php'];

        $this->fileDiscovery
            ->expects($this->once())
            ->method('discoverFiles')
            ->with([], 1)
            ->willReturn($files);

        $this->generator
            ->expects($this->once())
            ->method('generate')
            ->with($files, 'preload.php');

        $input = new Input('app:preload', [], ['output' => 'preload.php', 'min-hits' => '1']);
        $result = $this->handler->handle($input, $this->output);

        $this->assertSame(StatusCode::Ok, $result);
    }

    public function testHandleWithDryRunDoesNotGenerateScript(): void
    {
        $files = ['/path/to/file1.php', '/path/to/file2.php'];

        $this->fileDiscovery
            ->expects($this->once())
            ->method('discoverFiles')
            ->willReturn($files);

        $this->generator
            ->expects($this->never())
            ->method('generate');

        $input = new Input('app:preload', [], ['dry-run' => true]);
        $result = $this->handler->handle($input, $this->output);

        $this->assertSame(StatusCode::Ok, $result);
    }

    public function testHandleWithExcludePatternsPassesThemToDiscovery(): void
    {
        $excludePatterns = ['/vendor/', '/src/Legacy/'];

        $this->fileDiscovery
            ->expects($this->once())
            ->method('discoverFiles')
            ->with($excludePatterns, 1)
            ->willReturn(['/path/to/file.php']);

        $this->generator
            ->method('generate');

        $input = new Input('app:preload', [], ['exclude' => $excludePatterns, 'min-hits' => '1']);
        $result = $this->handler->handle($input, $this->output);

        $this->assertSame(StatusCode::Ok, $result);
    }

    public function testHandleWithMinHitsPassesToDiscovery(): void
    {
        $this->fileDiscovery
            ->expects($this->once())
            ->method('discoverFiles')
            ->with([], 5)
            ->willReturn(['/path/to/file.php']);

        $this->generator
            ->method('generate');

        $input = new Input('app:preload', [], ['min-hits' => '5']);
        $result = $this->handler->handle($input, $this->output);

        $this->assertSame(StatusCode::Ok, $result);
    }

    public function testHandleWithCustomOutputPath(): void
    {
        $this->fileDiscovery
            ->method('discoverFiles')
            ->willReturn(['/path/to/file.php']);

        $this->generator
            ->expects($this->once())
            ->method('generate')
            ->with(['/path/to/file.php'], '/custom/path/preload.php');

        $input = new Input('app:preload', [], ['output' => '/custom/path/preload.php']);
        $result = $this->handler->handle($input, $this->output);

        $this->assertSame(StatusCode::Ok, $result);
    }

    public function testHandleWithNoFilesReturnsOk(): void
    {
        $this->fileDiscovery
            ->method('discoverFiles')
            ->willReturn([]);

        $this->generator
            ->expects($this->never())
            ->method('generate');

        $input = new Input('app:preload', [], []);
        $result = $this->handler->handle($input, $this->output);

        $this->assertSame(StatusCode::Ok, $result);
    }

    public function testHandleWithDiscoveryExceptionReturnsError(): void
    {
        $this->fileDiscovery
            ->method('discoverFiles')
            ->willThrowException(new PreloadException('OPcache is not available'));

        $input = new Input('app:preload', [], []);
        $result = $this->handler->handle($input, $this->output);

        $this->assertSame(StatusCode::Error, $result);
    }

    public function testHandleWithGeneratorExceptionReturnsError(): void
    {
        $this->fileDiscovery
            ->method('discoverFiles')
            ->willReturn(['/path/to/file.php']);

        $this->generator
            ->method('generate')
            ->willThrowException(new PreloadException('Failed to write preload script'));

        $input = new Input('app:preload', [], []);
        $result = $this->handler->handle($input, $this->output);

        $this->assertSame(StatusCode::Error, $result);
    }
}
