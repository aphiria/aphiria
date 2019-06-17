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
use Aphiria\Net\Http\Handlers\IRequestHandler;
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

    public function testBuildingAppReturnsInstanceOfAppByDefault(): void
    {
        $this->setRouter();
        $this->assertInstanceOf(App::class, $this->appBuilder->build());
    }

    public function testComponentsAreCallableViaMagicMethods(): void
    {
        $this->appBuilder->registerComponentFactory('foo', function (array $callbacks){
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
        $this->appBuilder->build();
        $this->assertTrue($callbackWasRun);
    }

    public function testComponentNamesAreNormalized(): void
    {
        $this->appBuilder->registerComponentFactory('Foo', function (array $callbacks){
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
        $this->appBuilder->build();
        $this->assertTrue($callbackWasRun);
    }

    public function testNotRegisteringRouterThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Router callback not set');
        $this->appBuilder->build();
    }

    public function testRegisteringComponentExecutesAllRegisteredCallbacks(): void
    {
        $this->appBuilder->registerComponentFactory('foo', function (array $callbacks){
            foreach ($callbacks as $callback) {
                $callback();
            }
        });
        $callbackWasRun = false;
        $this->appBuilder->withComponent('foo', function () use (&$callbackWasRun) {
            $callbackWasRun = true;
        });
        $this->setRouter();
        $this->appBuilder->build();
        $this->assertTrue($callbackWasRun);
    }

    public function testRegisteringRouterThatIsNotRequestHandlerThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Router must implement ' . IRequestHandler::class);
        $this->appBuilder->withRouter(fn () => $this);
        $this->appBuilder->build();
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
        $this->appBuilder->build();
    }

    public function testWithComponentThrowsExceptionIfNoComponentFactoryIsRegistered(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('foo does not have a factory registered');
        $this->appBuilder->withComponent('foo', fn () => null);
    }

    public function testWithMethodsReturnsInstanceOfAppBuilder(): void
    {
        // Need to set up a component factory so we can call withComponent
        $this->appBuilder->registerComponentFactory('foo', fn (array $callbacks) => null);
        $bootstrapper = new class() extends Bootstrapper
        {
            public function registerBindings(IContainer $container): void
            {
                // Don't do anything
            }
        };
        $this->assertSame($this->appBuilder, $this->appBuilder->withBootstrappers(fn () => [$bootstrapper]));
        $this->assertSame($this->appBuilder, $this->appBuilder->withComponent('foo', fn (IContainer $container, array $callbacks) => null));
        $this->assertSame($this->appBuilder, $this->appBuilder->withMiddleware(fn () => []));
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
