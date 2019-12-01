<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ConsoleAnnotations\Tests;

use Aphiria\Collections\Tests\Mocks\MockObject;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\OptionTypes;
use Aphiria\Console\Output\IOutput;
use Aphiria\ConsoleAnnotations\Annotations\Argument;
use Aphiria\ConsoleAnnotations\Annotations\Command;
use Aphiria\ConsoleAnnotations\Annotations\Option;
use Aphiria\ConsoleAnnotations\ICommandHandlerResolver;
use Aphiria\ConsoleAnnotations\AnnotationCommandRegistrant;
use Aphiria\Reflection\ITypeFinder;
use PHPUnit\Framework\TestCase;

/**
 * Tests the reflection command annotation registrant
 */
class AnnotationCommandRegistrantTest extends TestCase
{
    private AnnotationCommandRegistrant $registrant;
    private CommandRegistry $commands;
    /** @var ICommandHandlerResolver|MockObject */
    private ICommandHandlerResolver $commandHandlerResolver;
    /** @var ITypeFinder|MockObject */
    private ITypeFinder $typeFinder;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->commandHandlerResolver = $this->createMock(ICommandHandlerResolver::class);
        $this->typeFinder = $this->createMock(ITypeFinder::class);
        $this->registrant = new AnnotationCommandRegistrant(
            __DIR__,
            $this->commandHandlerResolver,
            null,
            $this->typeFinder
        );
    }

    public function testRegisteringCommandWithAllPropertiesSetCreatesCommandWithAllPropertiesSet(): void
    {
        /**
         * @Command(
         *     "foo",
         *     arguments={@Argument("arg1", type=ArgumentTypes::REQUIRED, description="arg1 description", defaultValue="arg1 value")},
         *     options={@Option("opt1", shortName="o", type=OptionTypes::REQUIRED_VALUE, description="opt1 description", defaultValue="opt1 value")},
         *     description="command description",
         *     helpText="command help text"
         *  )
         */
        $commandHandler = new class implements ICommandHandler {
            public function handle(Input $input, IOutput $output)
            {
                return;
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllSubTypesOfType')
            ->with(ICommandHandler::class, [__DIR__])
            ->willReturn([\get_class($commandHandler)]);
        $this->registrant->registerCommands($this->commands);
        $this->assertCount(1, $this->commands->getAllCommands());

        // Command assertions
        $command = $this->commands->getAllCommands()[0];
        $this->assertEquals('foo', $command->name);
        $this->assertEquals('command description', $command->description);
        $this->assertEquals('command help text', $command->helpText);

        // Argument assertions
        $this->assertCount(1, $command->arguments);
        $arg1 = $command->arguments[0];
        $this->assertEquals('arg1', $arg1->name);
        $this->assertEquals(ArgumentTypes::REQUIRED, $arg1->type);
        $this->assertEquals('arg1 description', $arg1->description);
        $this->assertEquals('arg1 value', $arg1->defaultValue);

        // Option assertions
        $this->assertCount(1, $command->options);
        $opt1 = $command->options[0];
        $this->assertEquals('opt1', $opt1->name);
        $this->assertEquals('o', $opt1->shortName);
        $this->assertEquals(OptionTypes::REQUIRED_VALUE, $opt1->type);
        $this->assertEquals('opt1 description', $opt1->description);
        $this->assertEquals('opt1 value', $opt1->defaultValue);
    }
}
