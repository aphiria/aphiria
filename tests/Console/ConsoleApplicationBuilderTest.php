<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/configuration/blob/master/LICENSE.md
 */

namespace Aphiria\Configuration\Tests\Console;

use Aphiria\Configuration\Console\ConsoleApplicationBuilder;
use Aphiria\Configuration\Console\IConsoleModuleBuilder;
use Aphiria\Console\Commands\CommandRegistry;
use Opulence\Ioc\Bootstrappers\IBootstrapperRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Defines the console application builder tests
 */
class ConsoleApplicationBuilderTest extends TestCase
{
    /** @var CommandRegistry */
    private $commands;
    /** @var IBootstrapperRegistry|MockObject */
    private $bootstrappers;
    /** @var ConsoleApplicationBuilder */
    private $appBuilder;

    protected function setUp(): void
    {
        $this->commands = new CommandRegistry();
        $this->bootstrappers = $this->createMock(IBootstrapperRegistry::class);
        $this->appBuilder = new ConsoleApplicationBuilder($this->commands, $this->bootstrappers);
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
        /** @var IConsoleModuleBuilder|MockObject $module */
        $module = $this->createMock(IConsoleModuleBuilder::class);
        $module->expects($this->once())
            ->method('build')
            ->with($this->appBuilder);
        $this->appBuilder->withModule($module);
    }
}
