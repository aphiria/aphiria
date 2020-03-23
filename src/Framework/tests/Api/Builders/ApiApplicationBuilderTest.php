<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Builders;

use Aphiria\Api\Application;
use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\IModule;
use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Api\Builders\ApiApplicationBuilder;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the API application builder
 */
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
        $this->container->for(Application::class, function (IContainer $container) use ($router) {
            $container->bindInstance(IRequestHandler::class, $router);
        });
        $this->appBuilder->build();
        $this->assertNotSame($router, $this->container->resolve(IRequestHandler::class));
    }

    public function testBuildBuildsModulesBeforeComponentsAreInitialized(): void
    {
        $builtParts = [];
        $module = new class($builtParts) implements IModule
        {
            private array $builtParts;

            public function __construct(array &$builtParts)
            {
                $this->builtParts = &$builtParts;
            }

            public function build(IApplicationBuilder $appBuilder): void
            {
                $this->builtParts[] = \get_class($this);
            }
        };
        $component = new class($builtParts) implements IComponent
        {
            private array $builtParts;

            public function __construct(array &$builtParts)
            {
                $this->builtParts = &$builtParts;
            }

            public function build(): void
            {
                $this->builtParts[] = \get_class($this);
            }
        };
        // Purposely registering out of order to ensure that order does not matter
        $this->appBuilder->withComponent($component);
        $this->appBuilder->withModule($module);
        $this->container->for(Application::class, function (IContainer $container) {
            $container->bindInstance(IRequestHandler::class, $this->createMock(IRequestHandler::class));
        });
        $this->appBuilder->build();
        $this->assertEquals([\get_class($module), \get_class($component)], $builtParts);
    }

    public function testBuildWithoutHavingRouterBoundToContainerThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to build the API application');
        $this->appBuilder->build();
    }
}
