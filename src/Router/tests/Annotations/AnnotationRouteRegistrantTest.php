<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Annotations;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Reflection\ITypeFinder;
use Aphiria\Routing\Annotations\AnnotationRouteRegistrant;
use Aphiria\Routing\Annotations\Get;
use Aphiria\Routing\Annotations\Middleware;
use Aphiria\Routing\Annotations\RouteConstraint;
use Aphiria\Routing\Annotations\RouteGroup;
use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\Tests\Annotations\Mocks\DummyConstraint;
use Aphiria\Routing\Tests\Annotations\Mocks\DummyMiddleware;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the annotation route registrant
 */
class AnnotationRouteRegistrantTest extends TestCase
{
    private const PATH = __DIR__;
    private AnnotationRouteRegistrant $registrant;
    private Reader $reader;
    /** @var ITypeFinder|MockObject */
    private ITypeFinder $typeFinder;

    protected function setUp(): void
    {
        $this->reader = new AnnotationReader();
        $this->typeFinder = $this->createMock(ITypeFinder::class);
        $this->registrant = new AnnotationRouteRegistrant(self::PATH, $this->reader, $this->typeFinder);
    }

    public function testRegisteringRouteWithAllPropertiesSetCreatesRouteWithAllThosePropertiesSet(): void
    {
        $controller = new class extends Controller {
            /**
             * @Get("foo", host="example.com", name="routename", isHttpsOnly=true, attributes={"foo":"bar"}, constraints={@RouteConstraint(DummyConstraint::class, constructorParams={"param"})})
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
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
        $controller = new class extends Controller {
            /**
             * @Get("bar")
             * @Middleware(DummyMiddleware::class, attributes={"foo":"bar"})
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertCount(1, $route->middlewareBindings);
        $this->assertEquals(DummyMiddleware::class, $route->middlewareBindings[0]->className);
        $this->assertEquals(['foo' => 'bar'], $route->middlewareBindings[0]->attributes);
    }

    public function testRegisteringRouteWithMiddlewareThatIsInRouteGroupWithMiddlewareCreatesRouteWithBothMiddleware(): void
    {
        /**
         * @Middleware(DummyMiddleware::class, attributes={"foo":"bar"})
         */
        $controller = new class extends Controller {
            /**
             * @Get("bar")
             * @Middleware(DummyMiddleware::class, attributes={"baz":"blah"})
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertCount(2, $route->middlewareBindings);
        $this->assertEquals(DummyMiddleware::class, $route->middlewareBindings[0]->className);
        $this->assertEquals(['foo' => 'bar'], $route->middlewareBindings[0]->attributes);
        $this->assertEquals(DummyMiddleware::class, $route->middlewareBindings[1]->className);
        $this->assertEquals(['baz' => 'blah'], $route->middlewareBindings[1]->attributes);
    }

    public function testRegisteringRouteWithMultipleMiddlewareCreatesRouteWithThoseMiddleware(): void
    {
        $controller = new class extends Controller {
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
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
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
        $controller = new class extends Controller {
            /**
             * @Get("foo")
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertEquals('/foo', $route->uriTemplate->pathTemplate);
    }

    public function testRegisteringRoutesWithRouteGroupWithPathPrependsPathToRoutePaths(): void
    {
        /**
         * @RouteGroup("foo")
         */
        $controller = new class extends Controller {
            /**
             * @Get("bar")
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertEquals('/foo/bar', $route->uriTemplate->pathTemplate);
    }

    public function testRegisteringRoutesWithRouteGroupWithHostAppendsHostToRouteHost(): void
    {
        /**
         * @RouteGroup("", host="example.com")
         */
        $controller = new class extends Controller {
            /**
             * @Get("", host="api")
             */
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertEquals('api.example.com', $route->uriTemplate->hostTemplate);
    }

    public function testRegisteringRoutesWithRouteGroupThatIsHttpsOnlyMakesChildRoutesHttpsOnly(): void
    {
        /**
         * @RouteGroup("", isHttpsOnly=true)
         */
        $controller = new class extends Controller {
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
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(2, $routeArr);
        $this->assertTrue($routeArr[0]->uriTemplate->isHttpsOnly);
        $this->assertTrue($routeArr[1]->uriTemplate->isHttpsOnly);
    }

    public function testRegisteringRoutesWithRouteGroupWithAttributesAppliesAttributesToChildRoutes(): void
    {
        /**
         * @RouteGroup("", attributes={"foo":"bar"})
         */
        $controller = new class extends Controller {
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
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(2, $routeArr);
        $this->assertEquals(['foo' => 'bar'], $routeArr[0]->attributes);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $routeArr[1]->attributes);
    }

    public function testRegisteringRoutesWithRouteGroupWithConstraintsAppliesConstraintsToChildRoutes(): void
    {
        /**
         * @RouteGroup("", constraints={@RouteConstraint(DummyConstraint::class, constructorParams={"foo"})})
         */
        $controller = new class extends Controller {
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
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([\get_class($controller)]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(2, $routeArr);
        // Note: The HTTP method constraint gets automatically added, too
        $this->assertCount(2, $routeArr[0]->constraints);
        $this->assertCount(3, $routeArr[1]->constraints);
    }
}
