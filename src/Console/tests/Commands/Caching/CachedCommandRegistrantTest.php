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

use Aphiria\Console\Commands\Caching\ICommandRegistryCache;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Commands\Caching\CachedCommandRegistrant;
use Aphiria\Console\Commands\ICommandRegistrant;
use Closure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the cached command registrant
 */
class CachedCommandRegistrantTest extends TestCase
{
    public function testRegisteringCommandsWillIncludeCommandsInInitialRegistrant(): void
    {
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $commandCache = $this->createMock(ICommandRegistryCache::class);
        $commandCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $initialCommandRegistrant = new class ($expectedCommand, $expectedCommandHandlerFactory) implements ICommandRegistrant
        {
            private Command $expectedCommand;
            private Closure $expectedCommandHandlerFactory;

            public function __construct(Command $expectedCommand, \Closure $expectedCommandHandlerFactory)
            {
                $this->expectedCommand = $expectedCommand;
                $this->expectedCommandHandlerFactory = $expectedCommandHandlerFactory;
            }

            /**
             * @inheritdoc
             */
            public function registerCommands(CommandRegistry $commands): void
            {
                $commands->registerCommand($this->expectedCommand, $this->expectedCommandHandlerFactory);
            }
        };
        $cachedRegistrant = new CachedCommandRegistrant($commandCache, $initialCommandRegistrant);
        $commands = new CommandRegistry();
        $cachedRegistrant->registerCommands($commands);
        /** @var CommandBinding $actualCommandBinding */
        $actualCommandBinding = null;
        $this->assertTrue($commands->tryGetBinding('foo', $actualCommandBinding));
        $this->assertSame($expectedCommand, $actualCommandBinding->command);
        $this->assertSame($expectedCommandHandlerFactory, $actualCommandBinding->commandHandlerFactory);
    }

    public function testRegisteringCommandsWillIncludeCommandsInAddedRegistrant(): void
    {
        $commandCache = $this->createMock(ICommandRegistryCache::class);
        $commandCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $addedCommandRegistrant = new class ($expectedCommand, $expectedCommandHandlerFactory) implements ICommandRegistrant
        {
            private Command $expectedCommand;
            private Closure $expectedCommandHandlerFactory;

            public function __construct(Command $expectedCommand, \Closure $expectedCommandHandlerFactory)
            {
                $this->expectedCommand = $expectedCommand;
                $this->expectedCommandHandlerFactory = $expectedCommandHandlerFactory;
            }

            /**
             * @inheritdoc
             */
            public function registerCommands(CommandRegistry $commands): void
            {
                $commands->registerCommand($this->expectedCommand, $this->expectedCommandHandlerFactory);
            }
        };
        $cachedRegistrant = new CachedCommandRegistrant($commandCache);
        $cachedRegistrant->addCommandRegistrant($addedCommandRegistrant);
        $commands = new CommandRegistry();
        $cachedRegistrant->registerCommands($commands);
        /** @var CommandBinding $actualCommandBinding */
        $actualCommandBinding = null;
        $this->assertTrue($commands->tryGetBinding('foo', $actualCommandBinding));
        $this->assertSame($expectedCommand, $actualCommandBinding->command);
        $this->assertSame($expectedCommandHandlerFactory, $actualCommandBinding->commandHandlerFactory);
    }

    public function testRegisteringCommandsWithCacheThatHitsReturnsThoseCommands(): void
    {
        /** @var ICommandRegistryCache|MockObject $commandCache */
        $expectedCommands = new CommandRegistry();
        $expectedCommands->registerCommand(new Command('foo'), fn () => $this->createMock(ICommandHandler::class));
        $commandCache = $this->createMock(ICommandRegistryCache::class);
        $commandCache->expects($this->once())
            ->method('get')
            ->willReturn($expectedCommands);
        $commands = new CommandRegistry();
        $cachedRegistrant = new CachedCommandRegistrant($commandCache);
        $cachedRegistrant->registerCommands($commands);
        $this->assertCount(1, $commands->getAllCommands());
    }

    public function testRegisteringCommandsWithCacheThatMissesStillRunsTheRegistrants(): void
    {
        /** @var ICommandRegistryCache|MockObject $commandCache */
        $commandCache = $this->createMock(ICommandRegistryCache::class);
        $cachedRegistrant = new CachedCommandRegistrant($commandCache);
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $addedCommandRegistrant = new class ($expectedCommand, $expectedCommandHandlerFactory) implements ICommandRegistrant
        {
            private Command $expectedCommand;
            private Closure $expectedCommandHandlerFactory;

            public function __construct(Command $expectedCommand, \Closure $expectedCommandHandlerFactory)
            {
                $this->expectedCommand = $expectedCommand;
                $this->expectedCommandHandlerFactory = $expectedCommandHandlerFactory;
            }

            /**
             * @inheritdoc
             */
            public function registerCommands(CommandRegistry $commands): void
            {
                $commands->registerCommand($this->expectedCommand, $this->expectedCommandHandlerFactory);
            }
        };
        $cachedRegistrant->addCommandRegistrant($addedCommandRegistrant);
        $commandCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $commands = new CommandRegistry();
        $cachedRegistrant->registerCommands($commands);
        /** @var CommandBinding $actualCommandBinding */
        $actualCommandBinding = null;
        $this->assertTrue($commands->tryGetBinding('foo', $actualCommandBinding));
        $this->assertSame($expectedCommand, $actualCommandBinding->command);
        $this->assertSame($expectedCommandHandlerFactory, $actualCommandBinding->commandHandlerFactory);
    }

    public function testRegisteringCommandsWithCacheWillSetThemInCacheOnCacheMiss(): void
    {
        /** @var ICommandRegistryCache|MockObject $commandCache */
        $commandCache = $this->createMock(ICommandRegistryCache::class);
        $cachedRegistrant = new CachedCommandRegistrant($commandCache);
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $addedCommandRegistrant = new class ($expectedCommand, $expectedCommandHandlerFactory) implements ICommandRegistrant
        {
            private Command $expectedCommand;
            private Closure $expectedCommandHandlerFactory;

            public function __construct(Command $expectedCommand, \Closure $expectedCommandHandlerFactory)
            {
                $this->expectedCommand = $expectedCommand;
                $this->expectedCommandHandlerFactory = $expectedCommandHandlerFactory;
            }

            /**
             * @inheritdoc
             */
            public function registerCommands(CommandRegistry $commands): void
            {
                $commands->registerCommand($this->expectedCommand, $this->expectedCommandHandlerFactory);
            }
        };
        $cachedRegistrant->addCommandRegistrant($addedCommandRegistrant);
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
        $commands = new CommandRegistry();
        $cachedRegistrant->registerCommands($commands);
    }

    public function testRegisteringCommandsWithNoRegistrantsWillReturnEmptyRegistry(): void
    {
        $commandCache = $this->createMock(ICommandRegistryCache::class);
        $commandCache->expects($this->once())
            ->method('get')
            ->willReturn(null);
        $cachedRegistrant = new CachedCommandRegistrant($commandCache);
        $commands = new CommandRegistry();
        $cachedRegistrant->registerCommands($commands);
        $this->assertCount(0, $commands->getAllCommands());
    }
}
