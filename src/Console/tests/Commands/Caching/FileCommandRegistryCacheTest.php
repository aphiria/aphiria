<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Caching;

use Aphiria\Console\Commands\Caching\FileCommandRegistryCache;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use PHPUnit\Framework\TestCase;

/**
 * Tests the file command registry
 */
class FileCommandRegistryCacheTest extends TestCase
{
    /** @var string The path to the command cache */
    private const PATH = __DIR__ . '/tmp/command.cache';
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
        $this->assertFileNotExists(self::PATH);
    }

    public function testGetOnHitReturnsCommands(): void
    {
        // We are purposely testing setting every type of property inside the command to test that they're all unserializable
        $commands = new CommandRegistry();
        $commands->registerCommand(new Command('foo'), fn () => $this->createMock(ICommandHandler::class));
        $this->cache->set($commands);
        $this->assertEquals($commands, $this->cache->get());
    }

    public function testGetOnMissReturnsNull(): void
    {
        $this->assertNull($this->cache->get());
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
