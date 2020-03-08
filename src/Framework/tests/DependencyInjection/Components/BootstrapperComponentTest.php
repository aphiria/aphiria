<?php
/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\DependencyInjection\Components;

use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\DependencyInjection\Components\BootstrapperComponent;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the bootstrapper component
 */
class BootstrapperComponentTest extends TestCase
{
    private BootstrapperComponent $bootstrapperComponent;
    /** @var IBootstrapperDispatcher|MockObject */
    private IBootstrapperDispatcher $bootstrapperDispatcher;

    protected function setUp(): void
    {
        $this->bootstrapperDispatcher = $this->createMock(IBootstrapperDispatcher::class);
        $this->bootstrapperComponent = new BootstrapperComponent($this->bootstrapperDispatcher);
    }

    public function testInitializeWithBootstrappersAppendsToListOfBootstrappersToBeDispatched(): void
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
        $this->bootstrapperComponent->withBootstrappers($bootstrapper1);
        $this->bootstrapperComponent->withBootstrappers($bootstrapper2);
        $this->bootstrapperComponent->initialize();
    }

    public function testInitializeWithBootstrappersWithMultipleBootstrapperAddsThemToBootstrappersToBeDispatched(): void
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
        $this->bootstrapperComponent->withBootstrappers([$bootstrapper1, $bootstrapper2]);
        $this->bootstrapperComponent->initialize();
    }

    public function testInitializeWithBootstrappersWithSingleBootstrapperAddsItToBootstrappersToBeDispatched(): void
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
        $this->bootstrapperComponent->withBootstrappers($bootstrapper);
        $this->bootstrapperComponent->initialize();
    }
}
