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
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\DependencyInjection\Builders\BootstrapperBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the bootstrapper builder
 */
class BootstrapperBuilderTest extends TestCase
{
    private BootstrapperBuilder $bootstrapperBuilder;
    /** @var IBootstrapperDispatcher|MockObject */
    private IBootstrapperDispatcher $bootstrapperDispatcher;

    protected function setUp(): void
    {
        $this->bootstrapperDispatcher = $this->createMock(IBootstrapperDispatcher::class);
        $this->bootstrapperBuilder = new BootstrapperBuilder($this->bootstrapperDispatcher);
    }

    public function testWithBootstrappersAppendsToListOfBootstrappersToBeDispatched(): void
    {
        $bootstrapper1 = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $bootstrapper2 = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->bootstrapperDispatcher->expects($this->once())
            ->method('dispatch')
            ->with([$bootstrapper1, $bootstrapper2]);
        $this->bootstrapperBuilder->withBootstrappers($bootstrapper1);
        $this->bootstrapperBuilder->withBootstrappers($bootstrapper2);
        $this->bootstrapperBuilder->build($this->createMock(IApplicationBuilder::class));
    }

    public function testWithBootstrappersWithMultipleBootstrapperAddsThemToBootstrappersToBeDispatched(): void
    {
        $bootstrapper1 = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $bootstrapper2 = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->bootstrapperDispatcher->expects($this->once())
            ->method('dispatch')
            ->with([$bootstrapper1, $bootstrapper2]);
        $this->bootstrapperBuilder->withBootstrappers([$bootstrapper1, $bootstrapper2]);
        $this->bootstrapperBuilder->build($this->createMock(IApplicationBuilder::class));
    }

    public function testWithBootstrappersWithSingleBootstrapperAddsItToBootstrappersToBeDispatched(): void
    {
        $bootstrapper = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->bootstrapperDispatcher->expects($this->once())
            ->method('dispatch')
            ->with([$bootstrapper]);
        $this->bootstrapperBuilder->withBootstrappers($bootstrapper);
        $this->bootstrapperBuilder->build($this->createMock(IApplicationBuilder::class));
    }
}
