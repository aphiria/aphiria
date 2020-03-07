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

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Framework\Console\Builders\CommandBuilder;
use Aphiria\Framework\Console\Builders\CommandBuilderProxy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the command builder proxy
 */
class CommandBuilderProxyTest extends TestCase
{
    private CommandBuilderProxy $commandBuilderProxy;
    /** @var CommandBuilder|MockObject */
    private CommandBuilder $commandBuilder;

    protected function setUp(): void
    {
        $this->commandBuilder = $this->createMock(CommandBuilder::class);
        $this->commandBuilderProxy = new CommandBuilderProxy(
            fn () => $this->commandBuilder
        );
    }

    public function testBuildRegistersCommandsToProxiedComponentBuilder(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $expectedCommand = new Command('foo');
        $expectedCommandHandlerFactory = fn () => $this->createMock(ICommandHandler::class);
        $expectedCallback = fn (CommandRegistry $commands) => $commands->registerCommand($expectedCommand, $expectedCommandHandlerFactory);
        $this->commandBuilder->expects($this->at(0))
            ->method('withCommands')
            ->with($expectedCallback);
        $this->commandBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->commandBuilderProxy->withCommands($expectedCallback);
        $this->commandBuilderProxy->build($expectedAppBuilder);
    }

    public function testBuildWithAnnotationsConfiguresProxiedComponentBuilderToUseAnnotations(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $this->commandBuilder->expects($this->at(0))
            ->method('withAnnotations');
        $this->commandBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->commandBuilderProxy->withAnnotations();
        $this->commandBuilderProxy->build($expectedAppBuilder);
    }

    public function testGetProxiedTypeReturnsCorrectType(): void
    {
        $this->assertEquals(CommandBuilder::class, $this->commandBuilderProxy->getProxiedType());
    }
}
