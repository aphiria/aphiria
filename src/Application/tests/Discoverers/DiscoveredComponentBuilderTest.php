<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Application\Tests\Discoverers;

use Aphiria\Application\ApplicationBuilder;
use Aphiria\Application\Discoverers\ContainerServiceResolver;
use Aphiria\Application\Discoverers\DiscoveredComponentBuilder;
use Aphiria\Application\Discoverers\Routing\RouteComponentDiscoverer;
use Aphiria\Application\Discoverers\Routing\TestRouteComponentBuilder;
use Aphiria\Application\IApplication;
use Aphiria\Application\Tests\Discoverers\Delete\FooController;
use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\Routing\Components\RouterComponent;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DiscoveredComponentBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        if (Container::$globalInstance === null) {
            Container::$globalInstance = new Container();
        }
    }

    protected function tearDown(): void
    {
        Container::$globalInstance = null;
    }

    public function testFoo(): void
    {
        $appBuilder = new class () extends ApplicationBuilder {
            public function build(): IApplication
            {
                return new class () implements IApplication {
                    public function run(): int
                    {
                        return 0;
                    }
                };
            }
        };
        $serviceResolver = new ContainerServiceResolver(Container::$globalInstance, $appBuilder);
        $componentBuilder = new DiscoveredComponentBuilder(__DIR__ . '/Delete', $serviceResolver);
        $componentBuilder->build();
        $this->assertTrue(true);
    }

    public function testRouting(): void
    {
        $routerComponent = new RouterComponent(Container::$globalInstance);
        $routeDiscoverer = new RouteComponentDiscoverer();
        $routeBuilder = new TestRouteComponentBuilder($routerComponent);
        $discoveredComponents = $routeDiscoverer->discover(new ReflectionClass(FooController::class));
        $routeBuilder->build($discoveredComponents);
        $routerComponent->build();
        $this->assertTrue(true);
    }
}
