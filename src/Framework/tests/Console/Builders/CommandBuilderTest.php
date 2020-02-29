<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Builders;

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\Console\Commands\Annotations\AnnotationCommandRegistrant;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistrantCollection;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\DependencyInjection\IDependencyResolver;
use Aphiria\Framework\Console\Builders\CommandBuilder;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the command builder
 */
class CommandBuilderTest extends TestCase
{
    private CommandRegistry $commands;
    private CommandRegistrantCollection $commandRegistrants;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->commandRegistrants = new class() extends CommandRegistrantCollection
        {
            public function getAll(): array
            {
                return $this->commandRegistrants;
            }
        };
    }

    public function testBuildRegistersCommandsRegisteredInCallbacks(): void
    {
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $commandBuilder = new CommandBuilder($this->commands, $this->commandRegistrants);
        $commandBuilder->withCommands(fn (CommandRegistry $commands) => $commands->registerCommand($expectedCommand, $expectedCommandHandlerFactory));
        $commandBuilder->build($this->createMock(IApplicationBuilder::class));
        $this->assertCount(1, $this->commands->getAllCommandBindings());
        $this->assertSame($expectedCommand, $this->commands->getAllCommandBindings()[0]->command);
        $this->assertSame($expectedCommandHandlerFactory, $this->commands->getAllCommandBindings()[0]->commandHandlerFactory);
    }

    public function testWithAnnotationsAddsAnnotationRegistrant(): void
    {
        $annotationCommandRegistrant = new AnnotationCommandRegistrant(__DIR__, $this->createMock(IDependencyResolver::class));
        $commandBuilder = new CommandBuilder($this->commands, $this->commandRegistrants, $annotationCommandRegistrant);
        $commandBuilder->withAnnotations();
        $this->assertEquals([$annotationCommandRegistrant], $this->commandRegistrants->getAll());
    }

    public function testWithAnnotationsWithoutAnnotationRegistrantThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(AnnotationCommandRegistrant::class . ' cannot be null if using annotations');
        $commandBuilder = new CommandBuilder($this->commands, $this->commandRegistrants);
        $commandBuilder->withAnnotations();
    }
}
