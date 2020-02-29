<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\DependencyInjection\Builders;

use Aphiria\ApplicationBuilders\IApplicationBuilder;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\DependencyInjection\Builders\BootstrapperBuilder;
use Aphiria\Framework\DependencyInjection\Builders\BootstrapperBuilderProxy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the bootstrapper builder proxy
 */
class BootstrapperBuilderProxyTest extends TestCase
{
    private BootstrapperBuilderProxy $bootstrapperBuilderProxy;
    /** @var BootstrapperBuilder|MockObject */
    private BootstrapperBuilder $bootstrapperBuilder;

    protected function setUp(): void
    {
        $this->bootstrapperBuilder = $this->createMock(BootstrapperBuilder::class);
        $this->bootstrapperBuilderProxy = new BootstrapperBuilderProxy(
            fn () => $this->bootstrapperBuilder
        );
    }

    public function testBuildRegistersMultipleBootstrappersToProxiedComponentBuilder(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $expectedBootstrapper1 = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $expectedBootstrapper2 = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->bootstrapperBuilder->expects($this->at(0))
            ->method('withBootstrappers')
            ->with([$expectedBootstrapper1, $expectedBootstrapper2]);
        $this->bootstrapperBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->bootstrapperBuilderProxy->withBootstrappers([$expectedBootstrapper1, $expectedBootstrapper2]);
        $this->bootstrapperBuilderProxy->build($expectedAppBuilder);
    }

    public function testBuildRegistersSingleBootstrapperToProxiedComponentBuilder(): void
    {
        $expectedAppBuilder = $this->createMock(IApplicationBuilder::class);
        $expectedBootstrapper = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->bootstrapperBuilder->expects($this->at(0))
            ->method('withBootstrappers')
            ->with($expectedBootstrapper);
        $this->bootstrapperBuilder->expects($this->at(1))
            ->method('build')
            ->with($expectedAppBuilder);
        $this->bootstrapperBuilderProxy->withBootstrappers($expectedBootstrapper);
        $this->bootstrapperBuilderProxy->build($expectedAppBuilder);
    }

    public function testGetProxiedTypeReturnsCorrectType(): void
    {
        $this->assertEquals(BootstrapperBuilder::class, $this->bootstrapperBuilderProxy->getProxiedType());
    }
}
