<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Caching\ICommandRegistryCache;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Commands\LazyCommandRegistryFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the lazy command registry factory
 */
class LazyCommandRegistryFactoryTest extends TestCase
{
    public function testCreatingCommandsWillIncludeCommandsInInitialRegistrant(): void
    {
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $factory = new LazyCommandRegistryFactory(function (CommandRegistry $commands) use ($expectedCommand, $expectedCommandHandlerFactory) {
            $commands->registerCommand($expectedCommand, $expectedCommandHandlerFactory);
        });
        /** @var CommandBinding $actualCommandBinding */
        $actualCommandBinding = null;
        $this->assertTrue($factory->createCommands()->tryGetBinding('foo', $actualCommandBinding));
        $this->assertSame($expectedCommand, $actualCommandBinding->command);
        $this->assertSame($expectedCommandHandlerFactory, $actualCommandBinding->commandHandlerFactory);
    }

    public function testCreatingCommandsWillIncludeCommandsInAddedRegistrant(): void
    {
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $factory = new LazyCommandRegistryFactory();
        $factory->addCommandRegistrant(function (CommandRegistry $commands) use ($expectedCommand, $expectedCommandHandlerFactory) {
            $commands->registerCommand($expectedCommand, $expectedCommandHandlerFactory);
        });
        /** @var CommandBinding $actualCommandBinding */
        $actualCommandBinding = null;
        $this->assertTrue($factory->createCommands()->tryGetBinding('foo', $actualCommandBinding));
        $this->assertSame($expectedCommand, $actualCommandBinding->command);
        $this->assertSame($expectedCommandHandlerFactory, $actualCommandBinding->commandHandlerFactory);
    }

    public function testCreatingCommandsWithCacheThatHitsReturnsThoseCommands(): void
    {
        /** @var ICommandRegistryCache|MockObject $commandCache */
        $expectedCommands = new CommandRegistry();
        $commandCache = $this->createMock(ICommandRegistryCache::class);
        $commandCache->expects($this->once())
            ->method('get')
            ->willReturn($expectedCommands);
        $factory = new LazyCommandRegistryFactory(null, $commandCache);
        $this->assertSame($expectedCommands, $factory->createCommands());
    }

    public function testCreatingCommandsWithCacheThatMissesStillRunsTheRegistrants(): void
    {
        /** @var ICommandRegistryCache|MockObject $commandCache */
        $commandCache = $this->createMock(ICommandRegistryCache::class);
        $factory = new LazyCommandRegistryFactory(null, $commandCache);
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $factory->addCommandRegistrant(function (CommandRegistry $commands) use ($expectedCommand, $expectedCommandHandlerFactory) {
            $commands->registerCommand($expectedCommand, $expectedCommandHandlerFactory);
        });
        $commandCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        /** @var CommandBinding $actualCommandBinding */
        $actualCommandBinding = null;
        $this->assertTrue($factory->createCommands()->tryGetBinding('foo', $actualCommandBinding));
        $this->assertSame($expectedCommand, $actualCommandBinding->command);
        $this->assertSame($expectedCommandHandlerFactory, $actualCommandBinding->commandHandlerFactory);
    }

    public function testCreatingCommandsWithCacheWillSetThemInCacheOnCacheMiss(): void
    {
        /** @var ICommandRegistryCache|MockObject $commandCache */
        $commandCache = $this->createMock(ICommandRegistryCache::class);
        $factory = new LazyCommandRegistryFactory(null, $commandCache);
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $factory->addCommandRegistrant(function (CommandRegistry $commands) use ($expectedCommand, $expectedCommandHandlerFactory) {
            $commands->registerCommand($expectedCommand, $expectedCommandHandlerFactory);
        });
        $commandCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $commandCache->expects($this->once())
            ->method('set')
            ->with($this->callback(function (CommandRegistry $commands) use ($expectedCommand, $expectedCommandHandlerFactory) {
                /** @var CommandBinding $actualCommandBinding */
                $actualCommandBinding = null;

                return $commands->tryGetBinding('foo', $actualCommandBinding)
                    && $actualCommandBinding->command === $expectedCommand
                    && $actualCommandBinding->commandHandlerFactory === $expectedCommandHandlerFactory;
            }));
        $factory->createCommands();
    }

    public function testCreatingCommandsWithNoRegistrantsWillReturnEmptyRegistry(): void
    {
        $factory = new LazyCommandRegistryFactory();
        $this->assertCount(0, $factory->createCommands()->getAllCommands());
    }
}
