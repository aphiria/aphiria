<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Caching;

use Aphiria\Console\Commands\Caching\FileCommandRegistryCache;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FileCommandRegistryCacheTest extends TestCase
{
    /** @var string The path to the command cache */
    private const string PATH = __DIR__ . '/tmp/command.cache';
    private FileCommandRegistryCache $cache;

    protected function setUp(): void
    {
        $this->cache = new FileCommandRegistryCache(self::PATH);
    }

    protected function tearDown(): void
    {
        if (\file_exists(self::PATH)) {
            @\unlink(self::PATH);
        }
    }

    public function testFlushDeletesFile(): void
    {
        \file_put_contents(self::PATH, 'foo');
        $this->cache->flush();
        $this->assertFileDoesNotExist(self::PATH);
    }

    public function testGetOnHitReturnsCommands(): void
    {
        $commands = new CommandRegistry();
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $commands->registerCommand(new Command('foo'), $commandHandler::class);
        $this->cache->set($commands);
        $this->assertEquals($commands, $this->cache->get());
    }

    public function testGetOnMissReturnsNull(): void
    {
        $this->assertNull($this->cache->get());
    }

    public function testGettingFromCacheWithInvalidCachedDataThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Commands must be instance of ' . CommandRegistry::class . ' or null');
        \file_put_contents(self::PATH, '');
        $this->cache->get();
    }

    public function testHasReturnsExistenceOfFile(): void
    {
        $this->assertFalse($this->cache->has());
        \file_put_contents(self::PATH, 'foo');
        $this->assertTrue($this->cache->has());
    }

    public function testSetCreatesTheFile(): void
    {
        $this->cache->set(new CommandRegistry());
        $this->assertFileExists(self::PATH);
    }
}
