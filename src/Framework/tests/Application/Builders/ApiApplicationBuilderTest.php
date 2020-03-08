<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Application\Builders;

use Aphiria\Api\App;
use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\Application\Builders\IModuleBuilder;
use Aphiria\Application\IBootstrapper;
use Aphiria\Application\IComponent;
use Aphiria\DependencyInjection\Container;
use Aphiria\DependencyInjection\IContainer;
use Aphiria\Framework\Application\Builders\ApiApplicationBuilder;
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
        // TODO: Need to test bootstrappers
        $this->appBuilder = new ApiApplicationBuilder($this->container, []);
    }

    public function testBuildBindsApiApplicationToContainer(): void
    {
        // Bind the router to the container
        $router = $this->createMock(IRequestHandler::class);
        $this->container->for(App::class, function (IContainer $container) use ($router) {
            $container->bindInstance(IRequestHandler::class, $router);
        });
        $this->appBuilder->build();
        $this->assertNotSame($router, $this->container->resolve(IRequestHandler::class));
    }

    public function testBuildBootstrapsBootstrappers(): void
    {
        // Bind the router to the container
        $router = $this->createMock(IRequestHandler::class);
        $this->container->for(App::class, function (IContainer $container) use ($router) {
            $container->bindInstance(IRequestHandler::class, $router);
        });
        $bootstrapper = $this->createMock(IBootstrapper::class);
        $bootstrapper->expects($this->once())
            ->method('bootstrap');
        $appBuilder = new ApiApplicationBuilder($this->container, [$bootstrapper]);
        $appBuilder->build();
    }

    public function testBuildBuildsModulesBeforeComponentsAreInitialized(): void
    {
        $builtParts = [];
        $moduleBuilder = new class($builtParts) implements IModuleBuilder
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

            public function initialize(): void
            {
                $this->builtParts[] = \get_class($this);
            }
        };
        // Purposely registering out of order to ensure that order does not matter
        $this->appBuilder->withComponent($component);
        $this->appBuilder->withModuleBuilder($moduleBuilder);
        $this->container->for(App::class, function (IContainer $container) {
            $container->bindInstance(IRequestHandler::class, $this->createMock(IRequestHandler::class));
        });
        $this->appBuilder->build();
        $this->assertEquals([\get_class($moduleBuilder), \get_class($component)], $builtParts);
    }

    public function testBuildWithoutHavingRouterBoundToContainerThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No ' . IRequestHandler::class . ' router bound to the container');
        $this->appBuilder->build();
    }
}
