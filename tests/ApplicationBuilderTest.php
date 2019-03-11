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
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\LazyRouteFactory;
use Opulence\Ioc\Bootstrappers\IBootstrapperRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the application builder
 */
class ApplicationBuilderTest extends TestCase
{
    /** @var IBootstrapperRegistry|MockObject */
    private $bootstrappers;
    /** @var LazyRouteFactory */
    private $routeFactory;
    /** @var CommandRegistry */
    private $commands;
    /** @var ApplicationBuilder */
    private $appBuilder;

    protected function setUp(): void
    {
        $this->bootstrappers = $this->createMock(IBootstrapperRegistry::class);
        $this->routeFactory = new LazyRouteFactory();
        $this->commands = new CommandRegistry();
        $this->appBuilder = new ApplicationBuilder($this->bootstrappers, $this->routeFactory, $this->commands);
    }

    public function testBootstrapperDelegatesAreInvokedWithRegistryOnBuild(): void
    {
        $isInvoked = false;
        $this->appBuilder->withBootstrappers(function (IBootstrapperRegistry $bootstrappers) use (&$isInvoked) {
            $this->assertSame($this->bootstrappers, $bootstrappers);
            $isInvoked = true;
        });
        $this->appBuilder->build();
        $this->assertTrue($isInvoked);
    }

    public function testCommandDelegatesAreInvokedWithRegistryOnBuild(): void
    {
        $isInvoked = false;
        $this->appBuilder->withCommands(function (CommandRegistry $commands) use (&$isInvoked) {
            $this->assertSame($this->commands, $commands);
            $isInvoked = true;
        });
        $this->appBuilder->build();
        $this->assertTrue($isInvoked);
    }

    public function testRouteDelegatesAreInvokedWithRegistryOnBuild(): void
    {
        $isInvoked = false;
        $this->appBuilder->withRoutes(function (RouteBuilderRegistry $routes) use (&$isInvoked) {
            $isInvoked = true;
            $routes->map('GET', 'foo')
                ->toMethod('Foo', 'bar');
        });
        $this->appBuilder->build();
        $routes = $this->routeFactory->createRoutes();
        $this->assertTrue($isInvoked);
        $this->assertCount(1, $routes->getAll());
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
