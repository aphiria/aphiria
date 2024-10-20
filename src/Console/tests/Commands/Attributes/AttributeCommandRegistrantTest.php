<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Attributes;

use Aphiria\Console\Commands\Attributes\AttributeCommandRegistrant;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\ArgumentType;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\OptionType;
use Aphiria\Console\Output\IOutput;
use Aphiria\Console\Tests\Commands\Attributes\Mocks\CommandHandlerWithAllPropertiesSet;
use Aphiria\Console\Tests\Commands\Attributes\Mocks\CommandHandlerWithNonCommandAttribute;
use Aphiria\Reflection\ITypeFinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeCommandRegistrantTest extends TestCase
{
    private CommandRegistry $commands;
    private AttributeCommandRegistrant $registrant;
    private ITypeFinder&MockObject $typeFinder;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->typeFinder = $this->createMock(ITypeFinder::class);
        $this->registrant = new AttributeCommandRegistrant(__DIR__, $this->typeFinder);
    }

    public function testCommandHandlersWithNoCommandAttributesAreNotRegistered(): void
    {
        $commandHandler = new class () implements ICommandHandler {
            /**
             * @inheritdoc
             *
             * @return void
             */
            public function handle(Input $input, IOutput $output)
            {
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllSubTypesOfType')
            ->with(ICommandHandler::class, [__DIR__])
            ->willReturn([$commandHandler::class]);
        $this->registrant->registerCommands($this->commands);
        $this->assertEmpty($this->commands->commands);
    }

    public function testNonCommandAttributesAreIgnored(): void
    {
        $this->typeFinder->expects($this->once())
            ->method('findAllSubTypesOfType')
            ->with(ICommandHandler::class, [__DIR__])
            ->willReturn([CommandHandlerWithNonCommandAttribute::class]);
        $this->registrant->registerCommands($this->commands);
        $this->assertCount(1, $this->commands->commands);
    }

    public function testRegisteringCommandWithAllPropertiesSetCreatesCommandWithAllPropertiesSet(): void
    {
        $this->typeFinder->expects($this->once())
            ->method('findAllSubTypesOfType')
            ->with(ICommandHandler::class, [__DIR__])
            ->willReturn([CommandHandlerWithAllPropertiesSet::class]);
        $this->registrant->registerCommands($this->commands);
        $this->assertCount(1, $this->commands->commands);

        // Command assertions
        $command = $this->commands->commands[0];
        $this->assertSame('foo', $command->name);
        $this->assertSame('command description', $command->description);
        $this->assertSame('command help text', $command->helpText);

        // Argument assertions
        $this->assertCount(1, $command->arguments);
        $arg1 = $command->arguments[0];
        $this->assertSame('arg1', $arg1->name);
        $this->assertSame([ArgumentType::Required], $arg1->type);
        $this->assertSame('arg1 description', $arg1->description);
        $this->assertSame('arg1 value', $arg1->defaultValue);

        // Option assertions
        $this->assertCount(1, $command->options);
        $opt1 = $command->options[0];
        $this->assertSame('opt1', $opt1->name);
        $this->assertSame('o', $opt1->shortName);
        $this->assertSame([OptionType::RequiredValue], $opt1->type);
        $this->assertSame('opt1 description', $opt1->description);
        $this->assertSame('opt1 value', $opt1->defaultValue);
    }
}
