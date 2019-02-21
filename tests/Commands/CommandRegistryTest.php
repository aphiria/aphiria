<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command registry
 */
class CommandRegistryTest extends TestCase
{
    /** @var CommandRegistry */
    private $commands;

    public function setUp(): void
    {
        $this->commands = new CommandRegistry();
    }

    public function testGettingAllCommandsReturnsExpectedCommands(): void
    {
        $expectedCommand1 = new Command('command1', [], [], '');
        $expectedCommand2 = new Command('command2', [], [], '');
        $this->commands->registerManyCommands([
            new CommandBinding($expectedCommand1, $this->createMock(ICommandHandler::class)),
            new CommandBinding($expectedCommand2, $this->createMock(ICommandHandler::class))
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
        $this->commands->registerCommand($command, $expectedCommandHandler);
        $actualCommandHandler = null;
        $this->assertTrue($this->commands->tryGetHandler('FOO', $actualCommandHandler));
        $this->assertSame($expectedCommandHandler, $actualCommandHandler);
    }

    public function testRegisteringInvalidCommandHandlerThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $command = new Command('foo', [], [], '');
        $this->commands->registerCommand($command, 'invalid');
    }

    public function testRegisteringManyCommandsNormalizesNames(): void
    {
        $command = new Command('foo', [], [], '');
        $expectedCommandHandler = $this->createMock(ICommandHandler::class);
        $this->commands->registerManyCommands([
            new CommandBinding($command, $expectedCommandHandler)
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
        $this->commands->registerCommand($expectedCommand, $expectedCommandHandler);
        $actualBinding = null;
        $this->assertTrue($this->commands->tryGetBinding('foo', $actualBinding));
        $this->assertNotNull($actualBinding);
        $this->assertSame($expectedCommand, $actualBinding->command);
        $this->assertSame($expectedCommandHandler, $actualBinding->commandHandler);
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
        $commandHandler = $this->createMock(ICommandHandler::class);
        $this->commands->registerCommand($expectedCommand, $commandHandler);
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

    public function testTryGettingHandlerReturnsTrueIfCommandWasFound(): void
    {
        $expectedCommand = new Command('foo', [], [], '');
        $expectedCommandHandler = $this->createMock(ICommandHandler::class);
        $this->commands->registerCommand($expectedCommand, $expectedCommandHandler);
        $actualCommandHandler = null;
        $this->assertTrue($this->commands->tryGetHandler('foo', $actualCommandHandler));
        $this->assertSame($expectedCommandHandler, $actualCommandHandler);
    }
}
