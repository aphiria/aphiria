<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Routing\Builders;

use Aphiria\Application\Builders\IApplicationBuilder;
use Aphiria\DependencyInjection\Container;
use Aphiria\Framework\Routing\Builders\RouterBuilder;
use Aphiria\Routing\Annotations\AnnotationRouteRegistrant;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\RouteRegistrantCollection;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the router builder
 */
class RouterBuilderTest extends TestCase
{
    private RouteCollection $routes;
    private RouteRegistrantCollection $routeRegistrants;
    private Container $container;

    protected function setUp(): void
    {
        $this->routes = new RouteCollection();
        $this->routeRegistrants = new class() extends RouteRegistrantCollection
        {
            public function getAll(): array
            {
                return $this->routeRegistrants;
            }
        };
        // Use the real thing to simplify tests
        $this->container = new Container();
    }

    public function testBuildRegistersRoutesRegisteredInCallbacks(): void
    {
        $routerBuilder = new RouterBuilder($this->routes, $this->routeRegistrants, $this->container);
        $routerBuilder->withRoutes(fn (RouteBuilderRegistry $routeBuilders) => $routeBuilders->get('/foo')->toMethod('Foo', 'bar'));
        $routerBuilder->build($this->createMock(IApplicationBuilder::class));
        $this->assertCount(1, $this->routes->getAll());
        $this->assertEquals('/foo', $this->routes->getAll()[0]->uriTemplate->pathTemplate);
    }

    public function testWithAnnotationsAddsAnnotationRegistrant(): void
    {
        $annotationRouteRegistrant = new AnnotationRouteRegistrant(__DIR__);
        $routerBuilder = new RouterBuilder($this->routes, $this->routeRegistrants, $this->container, $annotationRouteRegistrant);
        $routerBuilder->withAnnotations();
        $this->assertEquals([$annotationRouteRegistrant], $this->routeRegistrants->getAll());
    }

    public function testWithAnnotationsWithoutAnnotationRegistrantThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(AnnotationRouteRegistrant::class . ' cannot be null if using annotations');
        $routerBuilder = new RouterBuilder($this->routes, $this->routeRegistrants, $this->container);
        $routerBuilder->withAnnotations();
    }
}
