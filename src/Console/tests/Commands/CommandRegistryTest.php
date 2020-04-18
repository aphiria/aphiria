<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CommandRegistryTest extends TestCase
{
    private CommandRegistry $commands;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
    }

    public function testCopyEffectivelyDuplicatesAnotherRegistry(): void
    {
        $registry1 = new CommandRegistry();
        $registry2 = new CommandRegistry();
        $expectedBinding = new CommandBinding(new Command('foo'), fn () => $this->createMock(ICommandHandler::class));
        $registry1->registerManyCommands([$expectedBinding]);
        $registry2->copy($registry1);
        $this->assertSame([$expectedBinding], $registry2->getAllCommandBindings());
    }

    public function testGettingAllCommandBindingsReturnsExpectedBindings(): void
    {
        $expectedBindings = [
            new CommandBinding(new Command('foo'), fn () => $this->createMock(ICommandHandler::class)),
            new CommandBinding(new Command('bar'), fn () => $this->createMock(ICommandHandler::class))
        ];
        $this->commands->registerManyCommands($expectedBindings);
        $actualBindings = $this->commands->getAllCommandBindings();
        $this->assertCount(2, $actualBindings);
        $this->assertSame($expectedBindings[0], $actualBindings[0]);
        $this->assertSame($expectedBindings[1], $actualBindings[1]);
    }

    public function testGettingAllCommandsReturnsExpectedCommands(): void
    {
        $expectedCommand1 = new Command('command1', [], [], '');
        $expectedCommand2 = new Command('command2', [], [], '');
        $this->commands->registerManyCommands([
            new CommandBinding(
                $expectedCommand1,
                fn () => $this->createMock(ICommandHandler::class)
            ),
            new CommandBinding(
                $expectedCommand2,
                fn () => $this->createMock(ICommandHandler::class)
            )
        ]);
        $actualCommands = $this->commands->getAllCommands();
        $this->assertCount(2, $actualCommands);
        $this->assertSame($expectedCommand1, $actualCommands[0]);
        $this->assertSame($expectedCommand2, $actualCommands[1]);
    }

    public function testRegisteringCommandNormalizesName(): void
    {
        $command = new Command('foo', [], [], '');
        $expectedCommandHandler = $this->createMock(ICommandHandler::class);
        $commandHandlerFactory = fn () => $expectedCommandHandler;
        $this->commands->registerCommand($command, $commandHandlerFactory);
        $actualCommandHandler = null;
        $this->assertTrue($this->commands->tryGetHandler('FOO', $actualCommandHandler));
        $this->assertSame($expectedCommandHandler, $actualCommandHandler);
    }

    public function testRegisteringManyCommandsNormalizesNames(): void
    {
        $command = new Command('foo', [], [], '');
        $expectedCommandHandler = $this->createMock(ICommandHandler::class);
        $commandHandlerFactory = fn () => $expectedCommandHandler;
        $this->commands->registerManyCommands([
            new CommandBinding($command, $commandHandlerFactory)
        ]);
        $actualCommandHandler = null;
        $this->assertTrue($this->commands->tryGetHandler('FOO', $actualCommandHandler));
        $this->assertSame($expectedCommandHandler, $actualCommandHandler);
    }

    public function testTryGettingBindingReturnsFalseIfNoCommandWasFound(): void
    {
        $binding = null;
        $this->assertFalse($this->commands->tryGetBinding('foo', $binding));
        $this->assertNull($binding);
    }

    public function testTryGettingBindingReturnsTrueIfCommandWasFound(): void
    {
        $expectedCommand = new Command('foo', [], [], '');
        $expectedCommandHandler = $this->createMock(ICommandHandler::class);
        $commandHandlerFactory = fn () => $expectedCommandHandler;
        $this->commands->registerCommand($expectedCommand, $commandHandlerFactory);
        /** @var CommandBinding|null $actualBinding */
        $actualBinding = null;
        $this->assertTrue($this->commands->tryGetBinding('foo', $actualBinding));
        $this->assertNotNull($actualBinding);
        $this->assertSame($expectedCommand, $actualBinding->command);
        $this->assertSame($expectedCommandHandler, $actualBinding->resolveCommandHandler());
    }

    public function testTryGettingCommandReturnsFalseIfNoCommandWasFound(): void
    {
        $command = null;
        $this->assertFalse($this->commands->tryGetCommand('foo', $command));
        $this->assertNull($command);
    }

    public function testTryGettingCommandReturnsTrueIfCommandWasFound(): void
    {
        $expectedCommand = new Command('foo', [], [], '');
        $this->commands->registerCommand(
            $expectedCommand,
            fn () => $this->createMock(ICommandHandler::class)
        );
        $actualCommand = null;
        $this->assertTrue($this->commands->tryGetCommand('foo', $actualCommand));
        $this->assertSame($expectedCommand, $actualCommand);
    }

    public function testTryGettingHandlerReturnsFalseIfNoCommandWasFound(): void
    {
        $commandHandler = null;
        $this->assertFalse($this->commands->tryGetHandler('foo', $commandHandler));
        $this->assertNull($commandHandler);
    }

    public function testTryGettingHandlerUsingCommandUsesTheCommandNameToLookItUp(): void
    {
        $command = new Command('foo');
        $expectedCommandHandler = $this->createMock(ICommandHandler::class);
        $this->commands->registerCommand($command, fn () => $expectedCommandHandler);
        $actualCommandHandler = null;
        $this->assertTrue($this->commands->tryGetHandler($command, $actualCommandHandler));
        $this->assertSame($expectedCommandHandler, $actualCommandHandler);
    }

    public function testTryGettingHandlerOfInvalidTypeThrowsInvalidArgumentException(): void
    {
        $commandHandler = null;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Command must be either a string or an instance of %s', Command::class));
        $this->commands->tryGetHandler(100, $commandHandler);
    }

    public function testTryGettingHandlerReturnsTrueIfCommandWasFound(): void
    {
        $expectedCommand = new Command('foo', [], [], '');
        $expectedCommandHandler = $this->createMock(ICommandHandler::class);
        $commandHandlerFactory = fn () => $expectedCommandHandler;
        $this->commands->registerCommand($expectedCommand, $commandHandlerFactory);
        $actualCommandHandler = null;
        $this->assertTrue($this->commands->tryGetHandler('foo', $actualCommandHandler));
        $this->assertSame($expectedCommandHandler, $actualCommandHandler);
    }
}
