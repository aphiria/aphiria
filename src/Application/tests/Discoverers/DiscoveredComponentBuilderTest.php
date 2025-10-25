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

use Aphiria\Application\Discoverers\DiscoveredComponentBuilder;
use Aphiria\Application\Discoverers\Routing\RouteComponentBuilder;
use Aphiria\Application\Discoverers\Routing\RouteComponentDiscoverer;
use Aphiria\Application\Tests\Discoverers\Delete\FooController;
use Aphiria\Routing\RouteCollection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class DiscoveredComponentBuilderTest extends TestCase
{
    public function testFoo(): void
    {
        $componentBuilder = new DiscoveredComponentBuilder(__DIR__ . '/Delete');
        $componentBuilder->build();
        $this->assertTrue(true);
    }

    public function testRouting(): void
    {
        $routes = new RouteCollection();
        $routeDiscoverer = new RouteComponentDiscoverer();
        $routeBuilder = new RouteComponentBuilder($routes);
        $discoveredComponents = $routeDiscoverer->discover(new ReflectionClass(FooController::class));
        $routeBuilder->build($discoveredComponents);
        $this->assertTrue(true);
    }
}
