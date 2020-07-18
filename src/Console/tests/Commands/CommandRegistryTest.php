<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands;

use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandBinding;
use Aphiria\Console\Commands\CommandRegistry;
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
        $expectedBinding = new CommandBinding(new Command('foo'), 'Foo');
        $registry1->registerManyCommands([$expectedBinding]);
        $registry2->copy($registry1);
        $this->assertSame([$expectedBinding], $registry2->getAllCommandBindings());
    }

    public function testGettingAllCommandBindingsReturnsExpectedBindings(): void
    {
        $expectedBindings = [
            new CommandBinding(new Command('foo'), 'Foo'),
            new CommandBinding(new Command('bar'), 'Bar')
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
            new CommandBinding($expectedCommand1, 'Handler1'),
            new CommandBinding($expectedCommand2, 'Handler2')
        ]);
        $actualCommands = $this->commands->getAllCommands();
        $this->assertCount(2, $actualCommands);
        $this->assertSame($expectedCommand1, $actualCommands[0]);
        $this->assertSame($expectedCommand2, $actualCommands[1]);
    }

    public function testRegisteringCommandNormalizesName(): void
    {
        $command = new Command('foo', [], [], '');
        $this->commands->registerCommand($command, 'Handler');
        $actualCommandHandlerClassName = null;
        $this->assertTrue($this->commands->tryGetHandlerClassName('FOO', $actualCommandHandlerClassName));
        $this->assertSame('Handler', $actualCommandHandlerClassName);
    }

    public function testRegisteringManyCommandsNormalizesNames(): void
    {
        $command = new Command('foo', [], [], '');
        $this->commands->registerManyCommands([
            new CommandBinding($command, 'Handler')
        ]);
        $actualCommandHandlerClassName = null;
        $this->assertTrue($this->commands->tryGetHandlerClassName('FOO', $actualCommandHandlerClassName));
        $this->assertSame('Handler', $actualCommandHandlerClassName);
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
        $this->commands->registerCommand($expectedCommand, 'Handler');
        /** @var CommandBinding|null $actualBinding */
        $actualBinding = null;
        $this->assertTrue($this->commands->tryGetBinding('foo', $actualBinding));
        $this->assertNotNull($actualBinding);
        $this->assertSame($expectedCommand, $actualBinding->command);
        $this->assertSame('Handler', $actualBinding->commandHandlerClassName);
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
        $this->commands->registerCommand($expectedCommand, 'Handler');
        $actualCommand = null;
        $this->assertTrue($this->commands->tryGetCommand('foo', $actualCommand));
        $this->assertSame($expectedCommand, $actualCommand);
    }

    public function testTryGettingHandlerClassNameReturnsFalseIfNoCommandWasFound(): void
    {
        $commandHandlerClassName = null;
        $this->assertFalse($this->commands->tryGetHandlerClassName('foo', $commandHandlerClassName));
        $this->assertNull($commandHandlerClassName);
    }

    public function testTryGettingHandlerClassNameUsingCommandUsesTheCommandNameToLookItUp(): void
    {
        $command = new Command('foo');
        $this->commands->registerCommand($command, 'Handler');
        $actualCommandHandlerClassName = null;
        $this->assertTrue($this->commands->tryGetHandlerClassName($command, $actualCommandHandlerClassName));
        $this->assertSame('Handler', $actualCommandHandlerClassName);
    }

    public function testTryGettingHandlerClassNameOfInvalidTypeThrowsInvalidArgumentException(): void
    {
        $commandHandlerClassName = null;
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Command must be either a string or an instance of %s', Command::class));
        $this->commands->tryGetHandlerClassName(100, $commandHandlerClassName);
    }

    public function testTryGettingHandlerClassNameReturnsTrueIfCommandWasFound(): void
    {
        $expectedCommand = new Command('foo', [], [], '');
        $this->commands->registerCommand($expectedCommand, 'Handler');
        $actualCommandHandler = null;
        $this->assertTrue($this->commands->tryGetHandlerClassName('foo', $actualCommandHandler));
        $this->assertSame('Handler', $actualCommandHandler);
    }
}
