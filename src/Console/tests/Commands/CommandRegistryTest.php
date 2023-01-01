<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
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
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $expectedBinding = new CommandBinding(new Command('foo'), $commandHandler::class);
        $registry1->registerManyCommands([$expectedBinding]);
        $registry2->copy($registry1);
        $this->assertSame([$expectedBinding], $registry2->getAllCommandBindings());
    }

    public function testGettingAllCommandBindingsReturnsExpectedBindings(): void
    {
        $commandHandler1 = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $commandHandler2 = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $expectedBindings = [
            new CommandBinding(new Command('foo'), $commandHandler1::class),
            new CommandBinding(new Command('bar'), $commandHandler2::class)
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
        $commandHandler1 = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $commandHandler2 = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerManyCommands([
            new CommandBinding($expectedCommand1, $commandHandler1::class),
            new CommandBinding($expectedCommand2, $commandHandler2::class)
        ]);
        $actualCommands = $this->commands->getAllCommands();
        $this->assertCount(2, $actualCommands);
        $this->assertSame($expectedCommand1, $actualCommands[0]);
        $this->assertSame($expectedCommand2, $actualCommands[1]);
    }

    public function testRegisteringCommandNormalizesName(): void
    {
        $command = new Command('foo', [], [], '');
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand($command, $commandHandler::class);
        $actualCommandHandlerClassName = null;
        $this->assertTrue($this->commands->tryGetHandlerClassName('FOO', $actualCommandHandlerClassName));
        $this->assertSame($commandHandler::class, $actualCommandHandlerClassName);
    }

    public function testRegisteringManyCommandsNormalizesNames(): void
    {
        $command = new Command('foo', [], [], '');
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerManyCommands([
            new CommandBinding($command, $commandHandler::class)
        ]);
        $actualCommandHandlerClassName = null;
        $this->assertTrue($this->commands->tryGetHandlerClassName('FOO', $actualCommandHandlerClassName));
        $this->assertSame($commandHandler::class, $actualCommandHandlerClassName);
    }

    public function testTryGettingBindingReturnsFalseIfNoCommandWasFound(): void
    {
        $binding = null;
        $this->assertFalse($this->commands->tryGetBinding('foo', $binding));
        /** @psalm-suppress TypeDoesNotContainNull We are specifically testing the case that getting the handler fails */
        $this->assertNull($binding);
    }

    public function testTryGettingBindingReturnsTrueIfCommandWasFound(): void
    {
        $expectedCommand = new Command('foo', [], [], '');
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand($expectedCommand, $commandHandler::class);
        /** @var CommandBinding|null $actualBinding */
        $actualBinding = null;
        $this->assertTrue($this->commands->tryGetBinding('foo', $actualBinding));
        /** @psalm-suppress RedundantCondition We are specifically testing that this is not null */
        $this->assertNotNull($actualBinding);
        $this->assertSame($expectedCommand, $actualBinding->command);
        $this->assertSame($commandHandler::class, $actualBinding->commandHandlerClassName);
    }

    public function testTryGettingCommandReturnsFalseIfNoCommandWasFound(): void
    {
        $command = null;
        $this->assertFalse($this->commands->tryGetCommand('foo', $command));
        /** @psalm-suppress TypeDoesNotContainNull We are specifically testing the case that getting the command fails */
        $this->assertNull($command);
    }

    public function testTryGettingCommandReturnsTrueIfCommandWasFound(): void
    {
        $expectedCommand = new Command('foo', [], [], '');
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand($expectedCommand, $commandHandler::class);
        $actualCommand = null;
        $this->assertTrue($this->commands->tryGetCommand('foo', $actualCommand));
        $this->assertSame($expectedCommand, $actualCommand);
    }

    public function testTryGettingHandlerClassNameReturnsFalseIfNoCommandWasFound(): void
    {
        $commandHandlerClassName = null;
        $this->assertFalse($this->commands->tryGetHandlerClassName('foo', $commandHandlerClassName));
        /** @psalm-suppress TypeDoesNotContainNull We are specifically testing the case that getting the handler fails */
        $this->assertNull($commandHandlerClassName);
    }

    public function testTryGettingHandlerClassNameUsingCommandUsesTheCommandNameToLookItUp(): void
    {
        $command = new Command('foo');
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand($command, $commandHandler::class);
        $actualCommandHandlerClassName = null;
        $this->assertTrue($this->commands->tryGetHandlerClassName($command, $actualCommandHandlerClassName));
        $this->assertSame($commandHandler::class, $actualCommandHandlerClassName);
    }

    public function testTryGettingHandlerClassNameReturnsTrueIfCommandWasFound(): void
    {
        $expectedCommand = new Command('foo', [], [], '');
        $commandHandler = new class () implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commands->registerCommand($expectedCommand, $commandHandler::class);
        $actualCommandHandler = null;
        $this->assertTrue($this->commands->tryGetHandlerClassName('foo', $actualCommandHandler));
        $this->assertSame($commandHandler::class, $actualCommandHandler);
    }
}
