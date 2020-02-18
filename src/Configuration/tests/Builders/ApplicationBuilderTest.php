<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Configuration\Tests\Builders;

use Aphiria\Configuration\Builders\ApplicationBuilder;
use Aphiria\Configuration\Builders\IModuleBuilder;
use Aphiria\DependencyInjection\Bootstrappers\Bootstrapper;
use Aphiria\DependencyInjection\Bootstrappers\IBootstrapperDispatcher;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\IDependencyResolver;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the application builder
 */
class ApplicationBuilderTest extends TestCase
{
    private Container $container;
    /** @var IBootstrapperDispatcher|MockObject */
    private IBootstrapperDispatcher $bootstrapperDispatcher;
    private ApplicationBuilder $appBuilder;

    protected function setUp(): void
    {
        // To simplify our tests, let's use a real container rather than a mocked interface
        $this->container = new Container();
        $this->container->bindInstance(MiddlewareCollection::class, new MiddlewareCollection());
        $this->container->bindInstance(IDependencyResolver::class, $this->container);

        $this->bootstrapperDispatcher = $this->createMock(IBootstrapperDispatcher::class);
        $this->appBuilder = new ApplicationBuilder($this->container, $this->bootstrapperDispatcher);
    }

    // TODO: Needs tests

    public function testNotRegisteringRouterThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Router callback not set');
        $this->appBuilder->buildApiApplication();
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
        $this->appBuilder->withBootstrapper($bootstrapper);
        $this->bootstrapperDispatcher->expects($this->once())
            ->method('dispatch')
            ->with([$bootstrapper]);
        $this->setRouter();
        $this->appBuilder->buildApiApplication();
    }

    public function testWithModuleBuildsTheModule(): void
    {
        /** @var IModuleBuilder|MockObject $module */
        $module = $this->createMock(IModuleBuilder::class);
        $module->expects($this->once())
            ->method('build')
            ->with($this->appBuilder);
        $this->appBuilder->withModuleBuilder($module);
    }

    /**
     * Sets a router for tests that need it
     */
    private function setRouter(): void
    {
        $this->appBuilder->withRouter(fn () => $this->createMock(IRequestHandler::class));
    }
}
