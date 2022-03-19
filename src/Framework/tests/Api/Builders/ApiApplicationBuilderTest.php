<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Builders;

use Aphiria\Api\Application;
use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\IComponent;
use Aphiria\Application\IModule;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\DependencyInjection\TargetedContext;
use Aphiria\Framework\Api\Builders\ApiApplicationBuilder;
use Aphiria\Net\Http\IRequestHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ApiApplicationBuilderTest extends TestCase
{
    private Container $container;
    private ApiApplicationBuilder $appBuilder;

    protected function setUp(): void
    {
        // To simplify testing, we'll use a real container
        $this->container = new Container();
        $this->appBuilder = new ApiApplicationBuilder($this->container);
    }

    public function testBuildBindsApiApplicationToContainer(): void
    {
        // Bind the router to the container
        $router = $this->createMock(IRequestHandler::class);
        $this->container->for(new TargetedContext(Application::class), function (IContainer $container) use ($router) {
            $container->bindInstance(IRequestHandler::class, $router);
        });
        $this->appBuilder->build();
        $this->assertNotSame($router, $this->container->resolve(IRequestHandler::class));
    }

    public function testBuildBuildsModulesBeforeComponentsAreInitialized(): void
    {
        $builtParts = [];
        /** @psalm-suppress UnusedVariable These variables are used by reference */
        $module = new class ($builtParts) implements IModule {
            private array $builtParts;

            public function __construct(array &$builtParts)
            {
                $this->builtParts = &$builtParts;
            }

            public function configure(IApplicationBuilder $appBuilder): void
            {
                $this->builtParts[] = $this::class;
            }
        };
        /** @psalm-suppress UnusedVariable These variables are used by reference */
        $component = new class ($builtParts) implements IComponent {
            private array $builtParts;

            public function __construct(array &$builtParts)
            {
                $this->builtParts = &$builtParts;
            }

            public function build(): void
            {
                $this->builtParts[] = $this::class;
            }
        };
        // Purposely registering out of order to ensure that order does not matter
        $this->appBuilder->withComponent($component);
        $this->appBuilder->withModule($module);
        $this->container->for(new TargetedContext(Application::class), function (IContainer $container) {
            $container->bindInstance(IRequestHandler::class, $this->createMock(IRequestHandler::class));
        });
        $this->appBuilder->build();
        $this->assertEquals([$module::class, $component::class], $builtParts);
    }

    public function testBuildWithoutHavingRouterBoundToContainerThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to build the API application');
        $this->appBuilder->build();
    }
}
