<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Caching;

use Aphiria\Console\Commands\Caching\FileCommandRegistryCache;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Tests\Commands\Mocks\MockCommandHandler;
use PHPUnit\Framework\TestCase;

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
        $this->assertFileDoesNotExist(self::PATH);
    }

    public function testGetOnHitReturnsCommands(): void
    {
        $commands = new CommandRegistry();
        // We are explicitly using an actual class here because Opis has trouble serializing mocks/anonymous classes
        $commands->registerCommand(new Command('foo'), fn () => new MockCommandHandler());
        // We have to clone the commands because serializing them will technically alter closure/serialized closure property values
        $expectedCommands = clone $commands;
        $this->cache->set($commands);
        $this->assertEquals($expectedCommands, $this->cache->get());
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
