<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Tests;

use Aphiria\Configuration\ApplicationBuilder;
use Aphiria\Configuration\IModuleBuilder;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\LazyRouteFactory;
use Opulence\Ioc\Bootstrappers\IBootstrapperRegistry;
use Opulence\Ioc\IContainer;
use Opulence\Ioc\IocException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the application builder
 */
class ApplicationBuilderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private $container;
    /** @var ApplicationBuilder */
    private $appBuilder;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->appBuilder = new ApplicationBuilder($this->container);
    }

    public function testBootstrapperCallbacksAreInvokedWithRegistryOnBuild(): void
    {
        $this->container->expects($this->at(0))
            ->method('resolve')
            ->with(LazyRouteFactory::class)
            ->willReturn(new LazyRouteFactory());
        $this->container->expects($this->at(1))
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willReturn(new CommandRegistry());
        $isInvoked = false;
        $this->appBuilder->withBootstrappers(function (IBootstrapperRegistry $bootstrappers) use (&$isInvoked) {
            $isInvoked = true;
        });
        $this->appBuilder->build();
        $this->assertTrue($isInvoked);
    }

    public function testCommandCallbacksAreInvokedWithRegistryOnBuild(): void
    {
        /** @var CommandRegistry|null $expectedCommands */
        $expectedCommands = null;
        $this->container->expects($this->at(0))
            ->method('resolve')
            ->with(LazyRouteFactory::class)
            ->willThrowException(new IocException());
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(LazyRouteFactory::class, $this->isInstanceOf(LazyRouteFactory::class));
        $this->container->expects($this->at(2))
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willThrowException(new IocException());
        $this->container->expects($this->at(3))
            ->method('bindInstance')
            ->with(
                CommandRegistry::class,
                $this->callback(function (CommandRegistry $actualCommands) use (&$expectedCommands) {
                    $expectedCommands = $actualCommands;

                    return true;
                })
            );
        $isInvoked = false;
        $this->appBuilder->withCommands(function (CommandRegistry $commands) use (&$isInvoked) {
            $isInvoked = true;
            // Register a dummy command se we can make sure that commands are really getting registered
            $commands->registerCommand(
                new Command('foo', [], [], ''),
                function () {
                    return $this->createMock(ICommandHandler::class);
                }
            );
        });
        $this->appBuilder->build();
        $this->assertTrue($isInvoked);
        $this->assertCount(1, $expectedCommands->getAllCommands());
    }

    public function testCommandCallbacksAreInvokedWithSameRegistryBoundInContainerOnBuild(): void
    {
        $expectedCommands = new CommandRegistry();
        $this->container->expects($this->at(0))
            ->method('resolve')
            ->with(LazyRouteFactory::class)
            ->willThrowException(new IocException());
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(LazyRouteFactory::class, $this->isInstanceOf(LazyRouteFactory::class));
        $this->container->expects($this->at(2))
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willReturn($expectedCommands);
        $isInvoked = false;
        $this->appBuilder->withCommands(function (CommandRegistry $commands) use (&$isInvoked, $expectedCommands) {
            $isInvoked = true;
            $this->assertSame($expectedCommands, $commands);
        });
        $this->appBuilder->build();
        $this->assertTrue($isInvoked);
    }

    public function testRouteCallbacksAreInvokedWithSameFactoryThatIsBoundInContainerOnBuild(): void
    {
        $expectedFactory = new LazyRouteFactory();
        $this->container->expects($this->at(0))
            ->method('resolve')
            ->with(LazyRouteFactory::class)
            ->willReturn($expectedFactory);
        $this->container->expects($this->at(1))
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willThrowException(new IocException());
        $this->container->expects($this->at(2))
            ->method('bindInstance')
            ->with(CommandRegistry::class, $this->isInstanceOf(CommandRegistry::class));
        $isInvoked = false;
        $this->appBuilder->withRoutes(function (RouteBuilderRegistry $routes) use (&$isInvoked) {
            $isInvoked = true;
            $routes->map('GET', 'foo')
                ->toMethod('Foo', 'bar');
        });
        $this->appBuilder->build();
        // We specifically have to create the routes before a lazy route factory is executed
        $this->assertCount(1, $expectedFactory->createRoutes()->getAll());
        $this->assertTrue($isInvoked);
    }

    public function testRouteCallbacksAreInvokedWithRegistryOnBuild(): void
    {
        /** @var LazyRouteFactory|null $expectedRouteFactory */
        $expectedRouteFactory = null;
        $this->container->expects($this->at(0))
            ->method('resolve')
            ->with(LazyRouteFactory::class)
            ->willThrowException(new IocException());
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(
                LazyRouteFactory::class,
                $this->callback(function (LazyRouteFactory $actualRouteFactory) use (&$expectedRouteFactory) {
                    // Hacky way of capturing the route factory we're using so we can later try to create its routes
                    $expectedRouteFactory = $actualRouteFactory;

                    return true;
                })
            );
        $this->container->expects($this->at(2))
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willThrowException(new IocException());
        $this->container->expects($this->at(3))
            ->method('bindInstance')
            ->with(CommandRegistry::class, $this->isInstanceOf(CommandRegistry::class));
        $isInvoked = false;
        $this->appBuilder->withRoutes(function (RouteBuilderRegistry $routes) use (&$isInvoked) {
            $isInvoked = true;
            $routes->map('GET', 'foo')
                ->toMethod('Foo', 'bar');
        });
        $this->appBuilder->build();
        // We specifically have to create the routes before a lazy route factory is executed
        $this->assertCount(1, $expectedRouteFactory->createRoutes()->getAll());
        $this->assertTrue($isInvoked);
    }

    public function testWithBootstrappersReturnsSelf(): void
    {
        $this->assertSame(
            $this->appBuilder,
            $this->appBuilder->withBootstrappers(function (IBootstrapperRegistry $bootstrappers) {
                // Don't do anything
            })
        );
    }

    public function testWithCommandsReturnsSelf(): void
    {
        $this->assertSame(
            $this->appBuilder,
            $this->appBuilder->withCommands(function (CommandRegistry $commands) {
                // Don't do anything
            })
        );
    }

    public function testWithModuleBuildsTheModule(): void
    {
        /** @var IModuleBuilder|MockObject $module */
        $module = $this->createMock(IModuleBuilder::class);
        $module->expects($this->once())
            ->method('build')
            ->with($this->appBuilder);
        $this->appBuilder->withModule($module);
    }

    public function testWithRoutesReturnsSelf(): void
    {
        $this->assertSame(
            $this->appBuilder,
            $this->appBuilder->withRoutes(function (RouteBuilderRegistry $routes) {
                // Don't do anything
            })
        );
    }
}
