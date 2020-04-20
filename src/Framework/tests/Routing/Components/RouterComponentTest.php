<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Routing\Components;

use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\Routing\Components\RouterComponent;
use Aphiria\Routing\Annotations\AnnotationRouteRegistrant;
use Aphiria\Routing\Builders\RouteCollectionBuilder;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteRegistrantCollection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class RouterComponentTest extends TestCase
{
    private RouterComponent $routerComponent;
    private Container $container;
    private RouteCollection $routes;
    private RouteRegistrantCollection $routeRegistrants;

    protected function setUp(): void
    {
        // Use a real container to simplify testing
        $this->container = new Container();
        $this->routerComponent = new RouterComponent($this->container);
        $this->container->bindInstance(RouteCollection::class, $this->routes = new RouteCollection());
        $this->routeRegistrants = new class() extends RouteRegistrantCollection {
            public function getAll(): array
            {
                return $this->routeRegistrants;
            }
        };
        $this->container->bindInstance(RouteRegistrantCollection::class, $this->routeRegistrants);
    }

    public function testBuildRegistersRoutesRegisteredInCallbacks(): void
    {
        $this->routerComponent->withRoutes(fn (RouteCollectionBuilder $routeBuilders) => $routeBuilders->get('/foo')->mapsToMethod('Foo', 'bar'));
        $this->routerComponent->build();
        $this->assertCount(1, $this->routes->getAll());
        $this->assertEquals('/foo', $this->routes->getAll()[0]->uriTemplate->pathTemplate);
    }

    public function testBuildWithAnnotationsAddsAnnotationRegistrant(): void
    {
        $annotationRouteRegistrant = new AnnotationRouteRegistrant(__DIR__);
        $this->container->bindInstance(AnnotationRouteRegistrant::class, $annotationRouteRegistrant);
        $this->routerComponent->withAnnotations();
        $this->routerComponent->build();
        // The first should be the annotation registrant, and the second the manually-registered route registrant
        $this->assertCount(2, $this->routeRegistrants->getAll());
        // Make sure that the annotation registrant is registered first
        $this->assertEquals($annotationRouteRegistrant, $this->routeRegistrants->getAll()[0]);
    }

    public function testBuildWithAnnotationsWithoutAnnotationRegistrantThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(AnnotationRouteRegistrant::class . ' cannot be null if using annotations');
        $this->routerComponent->withAnnotations();
        $this->routerComponent->build();
    }
}
