<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Attributes;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Middleware\Attributes\Middleware as MiddlewareLibraryMiddlewareAttribute;
use Aphiria\Reflection\ITypeFinder;
use Aphiria\Routing\Attributes\AttributeRouteRegistrant;
use Aphiria\Routing\Attributes\Get;
use Aphiria\Routing\Attributes\Middleware;
use Aphiria\Routing\Attributes\RouteConstraint;
use Aphiria\Routing\Attributes\RouteGroup;
use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\Tests\Attributes\Mocks\CustomMiddleware;
use Aphiria\Routing\Tests\Attributes\Mocks\DummyConstraint;
use Aphiria\Routing\Tests\Attributes\Mocks\DummyMiddleware;
use Aphiria\Routing\Tests\Attributes\Mocks\MiddlewareLibraryMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeRouteRegistrantTest extends TestCase
{
    private const PATH = __DIR__;
    private AttributeRouteRegistrant $registrant;
    private ITypeFinder&MockObject $typeFinder;

    protected function setUp(): void
    {
        $this->typeFinder = $this->createMock(ITypeFinder::class);
        $this->registrant = new AttributeRouteRegistrant(self::PATH, $this->typeFinder);
    }

    public function testRegisteringRouteForNonControllerRegistersNothing(): void
    {
        $nonController = new class () {
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$nonController::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $this->assertEmpty($routes->getAll());
    }

    public function testRegisteringRouteGroupWithMiddlewareLibraryMiddlewareIsAddedToRouteGroup(): void
    {
        $controller = new #[RouteGroup(''), MiddlewareLibraryMiddlewareAttribute(MiddlewareLibraryMiddleware::class)] class () extends Controller {
            #[Get('')]
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $this->assertCount(1, $routeArr[0]->middlewareBindings);
        $this->assertSame(MiddlewareLibraryMiddleware::class, $routeArr[0]->middlewareBindings[0]->className);
    }

    public function testRegisteringRouteGroupWithMiddlewareThatExtendsMiddlewareAttributeIsAddedToRouteGroup(): void
    {
        $controller = new #[RouteGroup(''), CustomMiddleware] class () extends Controller {
            #[Get('')]
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $this->assertCount(1, $routeArr[0]->middlewareBindings);
        $this->assertSame(DummyMiddleware::class, $routeArr[0]->middlewareBindings[0]->className);
    }

    public function testRegisteringRoutesWithRouteConstraintsAppliesConstraintsToChildRoutes(): void
    {
        $controller = new #[RouteConstraint(DummyConstraint::class, ['foo'])] class () extends Controller {
            #[Get('')]
            public function routeWithNoExtraConstraints(): void
            {
                // Empty
            }

            #[
                Get(''),
                /** @psalm-suppress ArgumentTypeCoercion https://github.com/vimeo/psalm/issues/4871 */
                RouteConstraint(DummyConstraint::class, ['bar'])
            ]
            public function routeWithExtraConstraints(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(2, $routeArr);
        // Note: The HTTP method constraint gets automatically added, too
        $this->assertCount(2, $routeArr[0]->constraints);
        $this->assertCount(3, $routeArr[1]->constraints);
    }

    public function testRegisteringRoutesWithRouteGroupThatIsHttpsOnlyMakesChildRoutesHttpsOnly(): void
    {
        $controller = new #[RouteGroup(isHttpsOnly: true)] class () extends Controller {
            #[Get('', isHttpsOnly: true)]
            public function routeThatIsAlreadyHttpsOnly(): void
            {
                // Empty
            }

            #[Get('')]
            public function routeThatIsNotHttpsOnly(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(2, $routeArr);
        $this->assertTrue($routeArr[0]->uriTemplate->isHttpsOnly);
        $this->assertTrue($routeArr[1]->uriTemplate->isHttpsOnly);
    }

    public function testRegisteringRoutesWithRouteGroupWithEmptyPathPrependsNothingToRoutePaths(): void
    {
        $controller = new #[RouteGroup('')] class () extends Controller {
            #[Get('foo')]
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertSame('/foo', $route->uriTemplate->pathTemplate);
    }

    public function testRegisteringRoutesWithRouteGroupWithHostAppendsHostToRouteHost(): void
    {
        $controller = new #[RouteGroup(host: 'example.com')] class () extends Controller {
            #[Get('', 'api')]
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertSame('api.example.com', $route->uriTemplate->hostTemplate);
    }

    public function testRegisteringRoutesWithRouteGroupWithParametersAppliesParametersToChildRoutes(): void
    {
        $controller = new #[RouteGroup('', parameters: ['foo' => 'bar'])] class () extends Controller {
            #[Get('')]
            public function routeWithNoParameters(): void
            {
                // Empty
            }

            #[Get('', parameters: ['baz' => 'blah'])]
            public function routeWithParameters(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(2, $routeArr);
        $this->assertEquals(['foo' => 'bar'], $routeArr[0]->parameters);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $routeArr[1]->parameters);
    }

    public function testRegisteringRoutesWithRouteGroupWithPathPrependsPathToRoutePaths(): void
    {
        $controller = new #[RouteGroup('foo')] class () extends Controller {
            #[Get('bar')]
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertSame('/foo/bar', $route->uriTemplate->pathTemplate);
    }

    public function testRegisteringRouteWithAllPropertiesSetCreatesRouteWithAllThosePropertiesSet(): void
    {
        $controller = new class () extends Controller {
            #[
                Get('foo', 'example.com', 'routename', true, ['foo' => 'bar']),
                RouteConstraint(DummyConstraint::class, ['param'])
            ]
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertSame('/foo', $route->uriTemplate->pathTemplate);
        $this->assertSame('example.com', $route->uriTemplate->hostTemplate);
        $this->assertTrue($route->uriTemplate->isHttpsOnly);
        $this->assertEquals(['foo' => 'bar'], $route->parameters);
        $this->assertCount(2, $route->constraints);
        $this->assertInstanceOf(HttpMethodRouteConstraint::class, $route->constraints[0]);
        $this->assertInstanceOf(DummyConstraint::class, $route->constraints[1]);
    }

    public function testRegisteringRouteWithMiddlewareCreatesRouteWithThatMiddleware(): void
    {
        $controller = new class () extends Controller {
            #[
                Get('bar'),
                Middleware(DummyMiddleware::class, ['foo' => 'bar'])
            ]
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertCount(1, $route->middlewareBindings);
        $this->assertSame(DummyMiddleware::class, $route->middlewareBindings[0]->className);
        $this->assertEquals(['foo' => 'bar'], $route->middlewareBindings[0]->parameters);
    }

    public function testRegisteringRouteWithMiddlewareFromMiddlewareLibraryCreatesRouteWithThatMiddleware(): void
    {
        $controller = new class () extends Controller {
            #[
                Get('bar'),
                MiddlewareLibraryMiddlewareAttribute(MiddlewareLibraryMiddleware::class, ['foo' => 'bar'])
            ]
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertCount(1, $route->middlewareBindings);
        $this->assertSame(MiddlewareLibraryMiddleware::class, $route->middlewareBindings[0]->className);
        $this->assertEquals(['foo' => 'bar'], $route->middlewareBindings[0]->parameters);
    }

    public function testRegisteringRouteWithMiddlewareThatExtendsMiddlewareAttributeIsAddedToRoute(): void
    {
        $controller = new class () extends Controller {
            #[
                Get(''),
                CustomMiddleware
            ]
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $this->assertCount(1, $routeArr[0]->middlewareBindings);
        $this->assertSame(DummyMiddleware::class, $routeArr[0]->middlewareBindings[0]->className);
    }

    public function testRegisteringRouteWithMiddlewareThatIsInRouteGroupWithMiddlewareCreatesRouteWithBothMiddleware(): void
    {
        $controller = new #[Middleware(DummyMiddleware::class, ['foo' => 'bar'])] class () extends Controller {
            #[
                Get('bar'),
                Middleware(DummyMiddleware::class, ['baz' => 'blah'])
            ]
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertCount(2, $route->middlewareBindings);
        $this->assertSame(DummyMiddleware::class, $route->middlewareBindings[0]->className);
        $this->assertEquals(['foo' => 'bar'], $route->middlewareBindings[0]->parameters);
        $this->assertSame(DummyMiddleware::class, $route->middlewareBindings[1]->className);
        $this->assertEquals(['baz' => 'blah'], $route->middlewareBindings[1]->parameters);
    }

    public function testRegisteringRouteWithMultipleMiddlewareCreatesRouteWithThoseMiddleware(): void
    {
        $controller = new class () extends Controller {
            #[
                Get('bar'),
                Middleware(DummyMiddleware::class, ['foo' => 'bar']),
                Middleware(DummyMiddleware::class, ['baz' => 'blah'])
            ]
            public function route(): void
            {
                // Empty
            }
        };
        $this->typeFinder->expects($this->once())
            ->method('findAllClasses')
            ->with([self::PATH])
            ->willReturn([$controller::class]);
        $routes = new RouteCollection();
        $this->registrant->registerRoutes($routes);
        $routeArr = $routes->getAll();
        $this->assertCount(1, $routeArr);
        $route = $routeArr[0];
        $this->assertCount(2, $route->middlewareBindings);
        $this->assertSame(DummyMiddleware::class, $route->middlewareBindings[0]->className);
        $this->assertEquals(['foo' => 'bar'], $route->middlewareBindings[0]->parameters);
        $this->assertSame(DummyMiddleware::class, $route->middlewareBindings[1]->className);
        $this->assertEquals(['baz' => 'blah'], $route->middlewareBindings[1]->parameters);
    }
}
