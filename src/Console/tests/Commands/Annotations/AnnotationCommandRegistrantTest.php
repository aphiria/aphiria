<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Commands\Annotations;

use Aphiria\Collections\Tests\Mocks\FakeObject;
use Aphiria\Console\Commands\Annotations\AnnotationCommandRegistrant;
use Aphiria\Console\Commands\Annotations\Argument;
use Aphiria\Console\Commands\Annotations\Command;
use Aphiria\Console\Commands\Annotations\Option;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\ArgumentTypes;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Input\OptionTypes;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Reflection\ITypeFinder;
use Doctrine\Common\Annotations\Annotation\Required;
use PHPUnit\Framework\TestCase;

class AnnotationCommandRegistrantTest extends TestCase
{
    private AnnotationCommandRegistrant $registrant;
    private CommandRegistry $commands;
    /** @var IServiceResolver|FakeObject */
    private IServiceResolver $commandHandlerResolver;
    /** @var ITypeFinder|FakeObject */
    private ITypeFinder $typeFinder;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->commandHandlerResolver = $this->createMock(IServiceResolver::class);
        $this->typeFinder = $this->createMock(ITypeFinder::class);
        $this->registrant = new AnnotationCommandRegistrant(
            __DIR__,
            null,
            $this->typeFinder
        );
    }

    public function testNonCommandAnnotationsAreIgnored(): void
    {
        /**
         * @Required
         * @Command("foo")
         */
        $commandHandler = new class() implements ICommandHandler {
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
        $commandHandler = new class() implements ICommandHandler {
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
        $this->assertSame('foo', $command->name);
        $this->assertSame('command description', $command->description);
        $this->assertSame('command help text', $command->helpText);

        // Argument assertions
        $this->assertCount(1, $command->arguments);
        $arg1 = $command->arguments[0];
        $this->assertSame('arg1', $arg1->name);
        $this->assertSame(ArgumentTypes::REQUIRED, $arg1->type);
        $this->assertSame('arg1 description', $arg1->description);
        $this->assertSame('arg1 value', $arg1->defaultValue);

        // Option assertions
        $this->assertCount(1, $command->options);
        $opt1 = $command->options[0];
        $this->assertSame('opt1', $opt1->name);
        $this->assertSame('o', $opt1->shortName);
        $this->assertSame(OptionTypes::REQUIRED_VALUE, $opt1->type);
        $this->assertSame('opt1 description', $opt1->description);
        $this->assertSame('opt1 value', $opt1->defaultValue);
    }
}
