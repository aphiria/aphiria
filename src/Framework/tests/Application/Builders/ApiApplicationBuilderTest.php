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
use Aphiria\Application\Builders\IComponentBuilder;
use Aphiria\Application\Builders\IModuleBuilder;
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
        $this->appBuilder = new ApiApplicationBuilder($this->container);
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

    public function testBuildBuildsModulesBeforeComponents(): void
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
        $componentBuilder = new class($builtParts) implements IComponentBuilder
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
        // Purposely registering out of order to ensure that order does not matter
        $this->appBuilder->withComponentBuilder($componentBuilder);
        $this->appBuilder->withModuleBuilder($moduleBuilder);
        $this->container->for(App::class, function (IContainer $container) {
            $container->bindInstance(IRequestHandler::class, $this->createMock(IRequestHandler::class));
        });
        $this->appBuilder->build();
        $this->assertEquals([\get_class($moduleBuilder), \get_class($componentBuilder)], $builtParts);
    }

    public function testBuildWithoutHavingRouterBoundToContainerThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No ' . IRequestHandler::class . ' router bound to the container');
        $this->appBuilder->build();
    }
}
