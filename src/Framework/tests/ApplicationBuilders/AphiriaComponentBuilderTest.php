<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\ApplicationBuilders;

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\ApplicationBuilders\AphiriaComponentBuilder;
use Aphiria\Framework\Console\Builders\CommandBuilder;
use Aphiria\Framework\Console\Builders\CommandBuilderProxy;
use Aphiria\Framework\DependencyInjection\Builders\BootstrapperBuilder;
use Aphiria\Framework\DependencyInjection\Builders\BootstrapperBuilderProxy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Aphiria component builder
 */
class AphiriaComponentBuilderTest extends TestCase
{
    private IContainer $container;
    /** @var IApplicationBuilder|MockObject */
    private IApplicationBuilder $appBuilder;
    private AphiriaComponentBuilder $componentBuilder;

    protected function setUp(): void
    {
        // Using a real instance to simplify testing
        $this->container = new Container();
        $this->componentBuilder = new AphiriaComponentBuilder($this->container);
        $this->appBuilder = $this->createMock(IApplicationBuilder::class);
    }

    public function testWithBootstrappersRegistersBootstrappersToComponentBuilder(): void
    {
        $bootstrapper = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $expectedBootstrapperBuilder = $this->createMock(BootstrapperBuilder::class);
        $expectedBootstrapperBuilder->expects($this->once())
            ->method('withBootstrappers')
            ->with($bootstrapper);
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(BootstrapperBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(BootstrapperBuilder::class)
            ->willReturn($expectedBootstrapperBuilder);
        $this->componentBuilder->withBootstrappers($this->appBuilder, $bootstrapper);
    }

    public function testWithBootstrappersRegistersCorrectComponentBuilder(): void
    {
        $bootstrapper = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(BootstrapperBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(BootstrapperBuilderProxy::class), 0)
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(BootstrapperBuilder::class)
            ->willReturn($this->createMock(BootstrapperBuilder::class));
        $this->componentBuilder->withBootstrappers($this->appBuilder, $bootstrapper);
    }

    public function testWithCommandAnnotationsConfiguresCommandBuilderToHaveAnnotations(): void
    {
        $expectedCommandBuilder = $this->createMock(CommandBuilder::class);
        $expectedCommandBuilder->expects($this->once())
            ->method('withAnnotations');
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn(true);
        $this->appBuilder->expects($this->at(1))
            ->method('getComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn($expectedCommandBuilder);
        $this->componentBuilder->withCommandAnnotations($this->appBuilder);
    }

    public function testWithCommandAnnotationsRegistersCorrectComponentBuilder(): void
    {
        $this->appBuilder->expects($this->at(0))
            ->method('hasComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn(false);
        $this->appBuilder->expects($this->at(1))
            ->method('withComponentBuilder')
            ->with($this->isInstanceOf(CommandBuilderProxy::class))
            ->willReturn($this->appBuilder);
        $this->appBuilder->expects($this->at(2))
            ->method('getComponentBuilder')
            ->with(CommandBuilder::class)
            ->willReturn($this->createMock(CommandBuilder::class));
        $this->componentBuilder->withCommandAnnotations($this->appBuilder);
    }
}
