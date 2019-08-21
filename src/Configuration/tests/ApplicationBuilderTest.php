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

use Aphiria\Api\App;
use Aphiria\Configuration\ApplicationBuilder;
use Aphiria\Configuration\IModuleBuilder;
use Aphiria\Configuration\Middleware\MiddlewareBinding;
use Aphiria\Console\Commands\Command;
use Aphiria\Console\Commands\CommandRegistry;
use Aphiria\Console\Commands\ICommandHandler;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use BadMethodCallException;
use Opulence\Ioc\Bootstrappers\Bootstrapper;
use Opulence\Ioc\Bootstrappers\IBootstrapperDispatcher;
use Opulence\Ioc\IContainer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the application builder
 */
class ApplicationBuilderTest extends TestCase
{
    /** @var IContainer|MockObject */
    private IContainer $container;
    /** @var IBootstrapperDispatcher|MockObject */
    private IBootstrapperDispatcher $bootstrapperDispatcher;
    private ApplicationBuilder $appBuilder;

    protected function setUp(): void
    {
        $this->container = $this->createMock(IContainer::class);
        $this->bootstrapperDispatcher = $this->createMock(IBootstrapperDispatcher::class);
        $this->appBuilder = new ApplicationBuilder($this->container, $this->bootstrapperDispatcher);
    }

    public function testBuildingApiReturnsInstanceOfAppByDefault(): void
    {
        $this->setRouter();
        $this->assertInstanceOf(App::class, $this->appBuilder->buildApiApplication());
    }

    public function testBuildingConsoleBindsNewCommandRegistryBoundToContainerIfNotAlreadyBound(): void
    {
        $this->container->expects($this->at(0))
            ->method('hasBinding')
            ->with(CommandRegistry::class)
            ->willReturn(false);
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(CommandRegistry::class, $commands = new CommandRegistry());
        $this->container->expects($this->at(2))
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willReturn($commands);
        $this->appBuilder->buildConsoleApplication();
    }

    public function testBuildingConsoleUsesCommandRegistryBoundToContainerIfAvailable(): void
    {
        $this->container->expects($this->at(0))
            ->method('hasBinding')
            ->with(CommandRegistry::class)
            ->willReturn(true);
        $this->container->expects($this->at(1))
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willReturn($commands = new CommandRegistry());
        $this->container->expects($this->at(2))
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willReturn($commands);
        $this->appBuilder->buildConsoleApplication();
    }

    public function testComponentsAreCallableViaMagicMethods(): void
    {
        $this->appBuilder->registerComponentBuilder('foo', function (array $callbacks){
            foreach ($callbacks as $callback) {
                $callback();
            }
        });
        $callbackWasRun = false;
        // This has a lowercase name
        $this->appBuilder->withFoo(function () use (&$callbackWasRun) {
            $callbackWasRun = true;
        });
        $this->setRouter();
        $this->appBuilder->buildApiApplication();
        $this->assertTrue($callbackWasRun);
    }

    public function testComponentNamesAreNormalized(): void
    {
        $this->appBuilder->registerComponentBuilder('Foo', function (array $callbacks){
            foreach ($callbacks as $callback) {
                $callback();
            }
        });
        $callbackWasRun = false;
        // This has a lowercase name
        $this->appBuilder->withComponent('foo', function () use (&$callbackWasRun) {
            $callbackWasRun = true;
        });
        $this->setRouter();
        $this->appBuilder->buildApiApplication();
        $this->assertTrue($callbackWasRun);
    }

    public function testMagicMethodThatDoesNotStartWithWithThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->appBuilder->magic();
    }

    public function testMagicMethodThatOnlyContainsWithThrowsException(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->appBuilder->with();
    }

    public function testNotRegisteringRouterThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Router callback not set');
        $this->appBuilder->buildApiApplication();
    }

    public function testRegisteringComponentExecutesAllRegisteredCallbacks(): void
    {
        $this->appBuilder->registerComponentBuilder('foo', function (array $callbacks){
            foreach ($callbacks as $callback) {
                $callback();
            }
        });
        $callbackWasRun = false;
        $this->appBuilder->withComponent('foo', function () use (&$callbackWasRun) {
            $callbackWasRun = true;
        });
        $this->setRouter();
        $this->appBuilder->buildApiApplication();
        $this->assertTrue($callbackWasRun);
    }

    public function testRegisteringRouterThatIsNotRequestHandlerThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Router must implement ' . IRequestHandler::class);
        $this->appBuilder->withRouter(fn () => $this);
        $this->appBuilder->buildApiApplication();
    }

    public function testWithBootstrapperDispatchesAllRegisteredBootstrappers(): void
    {
        $bootstrapper = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->appBuilder->withBootstrappers(fn () => [$bootstrapper]);
        $this->bootstrapperDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(fn (array $bootstrappers) => \count($bootstrappers) === 1 && $bootstrappers[0] === $bootstrapper));
        $this->setRouter();
        $this->appBuilder->buildApiApplication();
    }

    public function testWithComponentThrowsExceptionIfNoComponentFactoryIsRegistered(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('foo does not have a builder registered');
        $this->appBuilder->withComponent('foo', fn () => null);
    }

    public function testWithConsoleCommandPassesBoundCommandRegistryToRegisteredCallbacksWhenOneIsBoundToContainer(): void
    {
        $this->container->expects($this->at(0))
            ->method('hasBinding')
            ->with(CommandRegistry::class)
            ->willReturn(true);
        $expectedCommands = new CommandRegistry();
        $this->container->expects($this->at(1))
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willReturn($expectedCommands);
        $this->container->expects($this->at(2))
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willReturn($expectedCommands);
        $this->appBuilder->withConsoleCommands(function (CommandRegistry $commands) {
            $commands->registerCommand(
                new Command('foo', [], [], ''),
                fn () => $this->createMock(ICommandHandler::class)
            );
        });
        $this->appBuilder->buildConsoleApplication();
    }

    public function testWithConsoleCommandPassesNewCommandRegistryToRegisteredCallbacksWhenNonIsBoundToContainer(): void
    {
        $this->container->expects($this->at(0))
            ->method('hasBinding')
            ->with(CommandRegistry::class)
            ->willReturn(false);
        $this->container->expects($this->at(1))
            ->method('bindInstance')
            ->with(CommandRegistry::class, $this->isInstanceOf(CommandRegistry::class));
        $this->container->expects($this->at(2))
            ->method('resolve')
            ->with(CommandRegistry::class)
            ->willReturn(new CommandRegistry());
        $this->appBuilder->withConsoleCommands(function (CommandRegistry $commands) {
            $commands->registerCommand(
                new Command('foo', [], [], ''),
                fn () => $this->createMock(ICommandHandler::class)
            );
        });
        $this->appBuilder->buildConsoleApplication();
    }

    public function testWithGlobalMiddlewareThatIsNotMiddlewareBindingThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Middleware bindings must be an instance of '. MiddlewareBinding::class);
        $this->setRouter();
        $this->appBuilder->withGlobalMiddleware(fn () => [$this]);
        $this->appBuilder->buildApiApplication();
    }

    public function testWithMethodsReturnsInstanceOfAppBuilder(): void
    {
        // Need to set up a component factory so we can call withComponent
        $this->appBuilder->registerComponentBuilder('foo', fn (array $callbacks) => null);
        $bootstrapper = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->assertSame($this->appBuilder, $this->appBuilder->withBootstrappers(fn () => [$bootstrapper]));
        $this->assertSame($this->appBuilder, $this->appBuilder->withComponent('foo', fn (IContainer $container, array $callbacks) => null));
        $this->assertSame($this->appBuilder, $this->appBuilder->withGlobalMiddleware(fn () => []));
        $this->assertSame($this->appBuilder, $this->appBuilder->withModule($this->createMock(IModuleBuilder::class)));
        $this->assertSame($this->appBuilder, $this->appBuilder->withRouter(fn () => $this->createMock(IRequestHandler::class)));
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

    /**
     * Sets a router for tests that need it
     */
    private function setRouter(): void
    {
        $this->appBuilder->withRouter(fn () => $this->createMock(IRequestHandler::class));
    }
}
