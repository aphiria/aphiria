<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Components;

use Aphiria\Console\Commands\Annotations\AnnotationCommandRegistrant;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Framework\Console\Components\CommandComponent;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the command component
 */
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
        $this->container->bindInstance(CommandRegistrantCollection::class, $this->commandRegistrants = new class() extends CommandRegistrantCollection
        {
            public function getAll(): array
            {
                return $this->commandRegistrants;
            }
        });
    }

    public function testBuildRegistersCommandsRegisteredInCallbacks(): void
    {
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $this->commandComponent->withCommands(fn (CommandRegistry $commands) => $commands->registerCommand($expectedCommand, $expectedCommandHandlerFactory));
        $this->commandComponent->build();
        $this->assertCount(1, $this->commands->getAllCommandBindings());
        $this->assertSame($expectedCommand, $this->commands->getAllCommandBindings()[0]->command);
        $this->assertSame($expectedCommandHandlerFactory, $this->commands->getAllCommandBindings()[0]->commandHandlerFactory);
    }

    public function testBuildWithAnnotationsAddsAnnotationRegistrant(): void
    {
        $annotationCommandRegistrant = new AnnotationCommandRegistrant(__DIR__, $this->createMock(IServiceResolver::class));
        $this->container->bindInstance(AnnotationCommandRegistrant::class, $annotationCommandRegistrant);
        $this->commandComponent->withAnnotations();
        $this->commandComponent->build();
        // We should have two - one for annotations and another for manually-registered commands
        $this->assertCount(2, $this->commandRegistrants->getAll());
        // Make sure that annotations are registered first
        $this->assertEquals($annotationCommandRegistrant, $this->commandRegistrants->getAll()[0]);
    }

    public function testBuildWithAnnotationsWithoutAnnotationRegistrantThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(AnnotationCommandRegistrant::class . ' cannot be null if using annotations');
        $this->commandComponent->withAnnotations();
        $this->commandComponent->build();
    }
}
