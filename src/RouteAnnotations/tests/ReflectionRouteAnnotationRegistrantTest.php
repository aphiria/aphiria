<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\RouteAnnotations\Tests;

use Aphiria\Api\Controllers\Controller;
use Aphiria\RouteAnnotations\Annotations\Get;
use Aphiria\RouteAnnotations\Annotations\Middleware;
use Aphiria\RouteAnnotations\Annotations\RouteConstraint;
use Aphiria\RouteAnnotations\Annotations\RouteGroup;
use Aphiria\RouteAnnotations\IControllerFinder;
use Aphiria\RouteAnnotations\ReflectionRouteAnnotationRegistrant;
use Aphiria\RouteAnnotations\Tests\Mocks\DummyConstraint;
use Aphiria\RouteAnnotations\Tests\Mocks\DummyMiddleware;
use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Doctrine\Annotations\AnnotationReader;
use Doctrine\Annotations\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the reflection route annotation registrant
 */
class ReflectionRouteAnnotationRegistrantTest extends TestCase
{
    private const PATH = __DIR__;
    private ReflectionRouteAnnotationRegistrant $registrant;
    /** @var IControllerFinder|MockObject */
    private IControllerFinder $controllerFinder;
    private Reader $reader;
    private RouteBuilderRegistry $routeBuilders;

    protected function setUp(): void
    {
        $this->controllerFinder = $this->createMock(IControllerFinder::class);
        $this->reader = new AnnotationReader();
        $this->registrant = new ReflectionRouteAnnotationRegistrant(self::PATH, $this->controllerFinder, $this->reader);
        $this->routeBuilders = new RouteBuilderRegistry();
    }

    public function testRegisteringRouteWithAllPropertiesSetCreatesRouteWithAllThosePropertiesSet(): void
    {
        $controller = new class extends Controller
        {
            /**
             * @Get("foo", host="example.com", name="routename", isHttpsOnly=true, attributes={"foo":"bar"}, constraints={@RouteConstraint(DummyConstraint::class, constructorParams={"param"})})
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->controllerFinder->expects($this->once())
            ->method('findAll')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $this->registrant->registerRoutes($this->routeBuilders);
        $routes = $this->routeBuilders->buildAll();
        $this->assertCount(1, $routes);
        $route = $routes[0];
        $this->assertEquals('/foo', $route->uriTemplate->pathTemplate);
        $this->assertEquals('example.com', $route->uriTemplate->hostTemplate);
        $this->assertTrue($route->uriTemplate->isHttpsOnly);
        $this->assertEquals(['foo' => 'bar'], $route->attributes);
        $this->assertCount(2, $route->constraints);
        $this->assertInstanceOf(HttpMethodRouteConstraint::class, $route->constraints[0]);
        $this->assertInstanceOf(DummyConstraint::class, $route->constraints[1]);
    }

    public function testRegisteringRouteWithMiddlewareCreatesRouteWithThatMiddleware(): void
    {
        $controller = new class extends Controller
        {
            /**
             * @Get("bar")
             * @Middleware(DummyMiddleware::class, attributes={"foo":"bar"})
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->controllerFinder->expects($this->once())
            ->method('findAll')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $this->registrant->registerRoutes($this->routeBuilders);
        $routes = $this->routeBuilders->buildAll();
        $this->assertCount(1, $routes);
        $route = $routes[0];
        $this->assertCount(1, $route->middlewareBindings);
        $this->assertEquals(DummyMiddleware::class, $route->middlewareBindings[0]->className);
        $this->assertEquals(['foo' => 'bar'], $route->middlewareBindings[0]->attributes);
    }

    public function testRegisteringRouteWithMiddlewareThatIsInRouteGroupWithMiddlewareCreatesRouteWithBothMiddleware(): void
    {
        /**
         * @Middleware(DummyMiddleware::class, attributes={"foo":"bar"})
         */
        $controller = new class extends Controller
        {
            /**
             * @Get("bar")
             * @Middleware(DummyMiddleware::class, attributes={"baz":"blah"})
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->controllerFinder->expects($this->once())
            ->method('findAll')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $this->registrant->registerRoutes($this->routeBuilders);
        $routes = $this->routeBuilders->buildAll();
        $this->assertCount(1, $routes);
        $route = $routes[0];
        $this->assertCount(2, $route->middlewareBindings);
        $this->assertEquals(DummyMiddleware::class, $route->middlewareBindings[0]->className);
        $this->assertEquals(['foo' => 'bar'], $route->middlewareBindings[0]->attributes);
        $this->assertEquals(DummyMiddleware::class, $route->middlewareBindings[1]->className);
        $this->assertEquals(['baz' => 'blah'], $route->middlewareBindings[1]->attributes);
    }

    public function testRegisteringRouteWithMultipleMiddlewareCreatesRouteWithThoseMiddleware(): void
    {
        $controller = new class extends Controller
        {
            /**
             * @Get("bar")
             * @Middleware(DummyMiddleware::class, attributes={"foo":"bar"})
             * @Middleware(DummyMiddleware::class, attributes={"baz":"blah"})
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->controllerFinder->expects($this->once())
            ->method('findAll')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $this->registrant->registerRoutes($this->routeBuilders);
        $routes = $this->routeBuilders->buildAll();
        $this->assertCount(1, $routes);
        $route = $routes[0];
        $this->assertCount(2, $route->middlewareBindings);
        $this->assertEquals(DummyMiddleware::class, $route->middlewareBindings[0]->className);
        $this->assertEquals(['foo' => 'bar'], $route->middlewareBindings[0]->attributes);
        $this->assertEquals(DummyMiddleware::class, $route->middlewareBindings[1]->className);
        $this->assertEquals(['baz' => 'blah'], $route->middlewareBindings[1]->attributes);
    }

    public function testRegisteringRoutesWithRouteGroupWithEmptyPathPrependsNothingToRoutePaths(): void
    {
        /**
         * @RouteGroup("")
         */
        $controller = new class extends Controller
        {
            /**
             * @Get("foo")
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->controllerFinder->expects($this->once())
            ->method('findAll')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $this->registrant->registerRoutes($this->routeBuilders);
        $routes = $this->routeBuilders->buildAll();
        $this->assertCount(1, $routes);
        $route = $routes[0];
        $this->assertEquals('/foo', $route->uriTemplate->pathTemplate);
    }

    public function testRegisteringRoutesWithRouteGroupWithPathPrependsPathToRoutePaths(): void
    {
        /**
         * @RouteGroup("foo")
         */
        $controller = new class extends Controller
        {
            /**
             * @Get("bar")
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->controllerFinder->expects($this->once())
            ->method('findAll')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $this->registrant->registerRoutes($this->routeBuilders);
        $routes = $this->routeBuilders->buildAll();
        $this->assertCount(1, $routes);
        $route = $routes[0];
        $this->assertEquals('/foo/bar', $route->uriTemplate->pathTemplate);
    }

    public function testRegisteringRoutesWithRouteGroupWithHostAppendsHostToRouteHost(): void
    {
        /**
         * @RouteGroup("", host="example.com")
         */
        $controller = new class extends Controller
        {
            /**
             * @Get("", host="api")
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->controllerFinder->expects($this->once())
            ->method('findAll')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $this->registrant->registerRoutes($this->routeBuilders);
        $routes = $this->routeBuilders->buildAll();
        $this->assertCount(1, $routes);
        $route = $routes[0];
        $this->assertEquals('api.example.com', $route->uriTemplate->hostTemplate);
    }

    public function testRegisteringRoutesWithRouteGroupThatIsHttpsOnlyMakesChildRoutesHttpsOnly(): void
    {
        /**
         * @RouteGroup("", isHttpsOnly=true)
         */
        $controller = new class extends Controller
        {
            /**
             * @Get("", isHttpsOnly=true)
             */
            public function routeThatIsAlreadyHttpsOnly(): void
            {
                // Empty
            }

            /**
             * @Get("", isHttpsOnly=false)
             */
            public function routeThatIsNotHttpsOnly(): void
            {
                // Empty
            }
        };
        $this->controllerFinder->expects($this->once())
            ->method('findAll')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $this->registrant->registerRoutes($this->routeBuilders);
        $routes = $this->routeBuilders->buildAll();
        $this->assertCount(2, $routes);
        $this->assertTrue($routes[0]->uriTemplate->isHttpsOnly);
        $this->assertTrue($routes[1]->uriTemplate->isHttpsOnly);
    }

    public function testRegisteringRoutesWithRouteGroupWithAttributesAppliesAttributesToChildRoutes(): void
    {
        /**
         * @RouteGroup("", attributes={"foo":"bar"})
         */
        $controller = new class extends Controller
        {
            /**
             * @Get("")
             */
            public function routeWithNoAttributes(): void
            {
                // Empty
            }

            /**
             * @Get("", attributes={"baz":"blah"})
             */
            public function routeWithAttributes(): void
            {
                // Empty
            }
        };
        $this->controllerFinder->expects($this->once())
            ->method('findAll')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $this->registrant->registerRoutes($this->routeBuilders);
        $routes = $this->routeBuilders->buildAll();
        $this->assertCount(2, $routes);
        $this->assertEquals(['foo' => 'bar'], $routes[0]->attributes);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $routes[1]->attributes);
    }

    public function testRegisteringRoutesWithRouteGroupWithConstraintsAppliesConstraintsToChildRoutes(): void
    {
        /**
         * @RouteGroup("", constraints={@RouteConstraint(DummyConstraint::class, constructorParams={"foo"})})
         */
        $controller = new class extends Controller
        {
            /**
             * @Get("")
             */
            public function routeWithNoExtraConstraints(): void
            {
                // Empty
            }

            /**
             * @Get("", constraints={@RouteConstraint(DummyConstraint::class, constructorParams={"bar"})})
             */
            public function routeWithExtraConstraints(): void
            {
                // Empty
            }
        };
        $this->controllerFinder->expects($this->once())
            ->method('findAll')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $this->registrant->registerRoutes($this->routeBuilders);
        $routes = $this->routeBuilders->buildAll();
        $this->assertCount(2, $routes);
        // Note: The HTTP method constraint gets automatically added, too
        $this->assertCount(2, $routes[0]->constraints);
        $this->assertCount(3, $routes[1]->constraints);
    }
}
