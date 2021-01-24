<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Components;

use Aphiria\Console\Commands\Attributes\AttributeCommandRegistrant;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Console\Input\Input;
use Aphiria\Console\Output\IOutput;
use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\Console\Components\CommandComponent;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CommandComponentTest extends TestCase
{
    private Container $container;
    private CommandComponent $commandComponent;
    private CommandRegistry $commands;
    private CommandRegistrantCollection $commandRegistrants;

    protected function setUp(): void
    {
        // Using a real container to simplify testing
        $this->container = new Container();
        $this->commandComponent = new CommandComponent($this->container);

        $this->container->bindInstance(CommandRegistry::class, $this->commands = new CommandRegistry());
        $this->container->bindInstance(CommandRegistrantCollection::class, $this->commandRegistrants = new class() extends CommandRegistrantCollection {
            public function getAll(): array
            {
                return $this->commandRegistrants;
            }
        });
    }

    public function testBuildRegistersCommandsRegisteredInCallbacks(): void
    {
        $expectedCommand = new Command('foo');
        $commandHandler = new class() implements ICommandHandler {
            public function handle(Input $input, IOutput $output): void
            {
            }
        };
        $this->commandComponent->withCommands(fn (CommandRegistry $commands) => $commands->registerCommand($expectedCommand, $commandHandler::class));
        $this->commandComponent->build();
        $this->assertCount(1, $this->commands->getAllCommandBindings());
        $this->assertSame($expectedCommand, $this->commands->getAllCommandBindings()[0]->command);
        $this->assertSame($commandHandler::class, $this->commands->getAllCommandBindings()[0]->commandHandlerClassName);
    }

    public function testBuildWithAttributesAddsAttributeRegistrant(): void
    {
        $attributeCommandRegistrant = new AttributeCommandRegistrant(__DIR__);
        $this->container->bindInstance(AttributeCommandRegistrant::class, $attributeCommandRegistrant);
        $this->commandComponent->withAttributes();
        $this->commandComponent->build();
        // We should have two - one for attributes and another for manually-registered commands
        $this->assertCount(2, $this->commandRegistrants->getAll());
        // Make sure that attributes are registered first
        $this->assertEquals($attributeCommandRegistrant, $this->commandRegistrants->getAll()[0]);
    }

    public function testBuildWithAttributesWithoutAttributeRegistrantThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(AttributeCommandRegistrant::class . ' cannot be null if using attributes');
        $this->commandComponent->withAttributes();
        $this->commandComponent->build();
    }
}
