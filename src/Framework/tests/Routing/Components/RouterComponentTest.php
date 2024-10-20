<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Routing\Components;

use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\Routing\Components\RouterComponent;
use Aphiria\Routing\Attributes\AttributeRouteRegistrant;
use Aphiria\Routing\RouteBuilder;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteCollectionBuilder;
use Aphiria\Routing\RouteRegistrantCollection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RouterComponentTest extends TestCase
{
    private Container $container;
    private RouterComponent $routerComponent;
    private RouteRegistrantCollection $routeRegistrants;
    private RouteCollection $routes;

    protected function setUp(): void
    {
        // Use a real container to simplify testing
        $this->container = new Container();
        $this->routerComponent = new RouterComponent($this->container);
        $this->container->bindInstance(RouteCollection::class, $this->routes = new RouteCollection());
        $this->routeRegistrants = new class () extends RouteRegistrantCollection {
            public array $values {
                get => $this->routeRegistrants;
            }
        };
        $this->container->bindInstance(RouteRegistrantCollection::class, $this->routeRegistrants);
    }

    public function testBuildRegistersRoutesRegisteredInCallbacks(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routerComponent->withRoutes(fn (RouteCollectionBuilder $routeBuilders): RouteBuilder => $routeBuilders->get('/foo')->mapsToMethod($controller::class, 'bar'));
        $this->routerComponent->build();
        $this->assertCount(1, $this->routes->values);
        $this->assertSame('/foo', $this->routes->values[0]->uriTemplate->pathTemplate);
    }

    public function testBuildWithAttributesAddsAttributeRegistrant(): void
    {
        $attributeRouteRegistrant = new AttributeRouteRegistrant(__DIR__);
        $this->container->bindInstance(AttributeRouteRegistrant::class, $attributeRouteRegistrant);
        $this->routerComponent->withAttributes();
        $this->routerComponent->build();
        // The first should be the attribute registrant, and the second the manually-registered route registrant
        $this->assertCount(2, $this->routeRegistrants->values);
        // Make sure that the attribute registrant is registered first
        $this->assertEquals($attributeRouteRegistrant, $this->routeRegistrants->values[0]);
    }

    public function testBuildWithAttributesWithoutAttributeRegistrantThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(AttributeRouteRegistrant::class . ' cannot be null if using attributes');
        $this->routerComponent->withAttributes();
        $this->routerComponent->build();
    }
}
