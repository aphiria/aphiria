<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Builders;

use Aphiria\Routing\Builders\RouteBuilder;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\UriTemplates\UriTemplate;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RouteBuilderTest extends TestCase
{
    private RouteBuilder $routeBuilder;

    protected function setUp(): void
    {
        $this->routeBuilder = new RouteBuilder(['GET'], new UriTemplate('/foo', 'example.com'));
    }

    public function testBuildingRouteBeforeSettingActionThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No controller specified for route');
        $this->routeBuilder->build();
    }

    public function testChainingOnFluentMethodsReturnsCorrectInstance(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $middleware = new class () {
        };
        $this->assertSame($this->routeBuilder, $this->routeBuilder->mapsToMethod($controller::class, 'bar'));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withParameter('foo', 'bar'));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withManyParameters(['foo' => 'bar']));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withManyMiddleware([$middleware::class]));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withMiddleware($middleware::class));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withName('Foo'));
    }

    public function testConstraintBindingIsSet(): void
    {
        /** @var IRouteConstraint&MockObject $constraint */
        $constraint = $this->createMock(IRouteConstraint::class);
        $this->routeBuilder->withConstraint($constraint);
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routeBuilder->mapsToMethod($controller::class, 'bar');
        $route = $this->routeBuilder->build();
        $this->assertContains($constraint, $route->constraints);
    }

    /**
     * @psalm-suppress InvalidArgument We are purposely testing passing in an invalid parameter
     */
    public function testInvalidManyMiddlewareThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Middleware binding must either be a string or an instance of %s', MiddlewareBinding::class));
        $this->routeBuilder->withManyMiddleware([$this]);
    }

    public function testManyMiddlewareBindingsAreSetWhenPassingThemInAsObjects(): void
    {
        $middleware1 = new class () {
        };
        $middleware2 = new class () {
        };
        $this->routeBuilder->withManyMiddleware([
            new MiddlewareBinding($middleware1::class, ['bar' => 'baz']),
            new MiddlewareBinding($middleware2::class, ['young' => 'cool']),
        ]);
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routeBuilder->mapsToMethod($controller::class, 'bar');
        $route = $this->routeBuilder->build();
        $this->assertCount(2, $route->middlewareBindings);
        $this->assertSame($middleware1::class, $route->middlewareBindings[0]->className);
        $this->assertSame($middleware2::class, $route->middlewareBindings[1]->className);
        $this->assertEquals(['bar' => 'baz'], $route->middlewareBindings[0]->parameters);
        $this->assertEquals(['young' => 'cool'], $route->middlewareBindings[1]->parameters);
    }

    public function testManyConstraintBindingIsSet(): void
    {
        $constraints = [$this->createMock(IRouteConstraint::class)];
        $this->routeBuilder->withManyConstraints($constraints);
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routeBuilder->mapsToMethod($controller::class, 'bar');
        $route = $this->routeBuilder->build();
        $this->assertContains($constraints[0], $route->constraints);
    }

    public function testManyMiddlewareBindingsAreSetWhenPassingThemInAsStrings(): void
    {
        $this->routeBuilder->withManyMiddleware(['foo', 'bar']);
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routeBuilder->mapsToMethod($controller::class, 'bar');
        $route = $this->routeBuilder->build();
        $this->assertCount(2, $route->middlewareBindings);
        $this->assertSame('foo', $route->middlewareBindings[0]->className);
        $this->assertSame('bar', $route->middlewareBindings[1]->className);
        $this->assertEquals([], $route->middlewareBindings[0]->parameters);
        $this->assertEquals([], $route->middlewareBindings[1]->parameters);
    }

    public function testMethodIsSetWhenUsingMethodAction(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routeBuilder->mapsToMethod($controller::class, 'bar');
        $route = $this->routeBuilder->build();
        $this->assertSame($controller::class, $route->action->className);
        $this->assertSame('bar', $route->action->methodName);
    }

    public function testMiddlewareBindingIsSet(): void
    {
        $middleware = new class () {
        };
        $this->routeBuilder->withMiddleware($middleware::class, ['bar' => 'baz']);
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routeBuilder->mapsToMethod($controller::class, 'bar');
        $route = $this->routeBuilder->build();
        $this->assertCount(1, $route->middlewareBindings);
        $this->assertSame($middleware::class, $route->middlewareBindings[0]->className);
        $this->assertEquals(['bar' => 'baz'], $route->middlewareBindings[0]->parameters);
    }

    public function testNameIsSet(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $route = $this->routeBuilder->mapsToMethod($controller::class, 'bar')
            ->withName('foo')
            ->build();
        $this->assertSame('foo', $route->name);
    }

    public function testParametersAreSetWhenPassingIndividualParameters(): void
    {
        $this->routeBuilder->withParameter('foo', 'bar');
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routeBuilder->mapsToMethod($controller::class, 'bar');
        $route = $this->routeBuilder->build();
        $this->assertEquals(['foo' => 'bar'], $route->parameters);
    }

    public function testParametersAreSetWhenPassingMultipleParameters(): void
    {
        $this->routeBuilder->withManyParameters(['foo' => 'bar']);
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routeBuilder->mapsToMethod($controller::class, 'bar');
        $route = $this->routeBuilder->build();
        $this->assertEquals(['foo' => 'bar'], $route->parameters);
    }

    public function testUriTemplateIsSet(): void
    {
        $expectedUriTemplate = new UriTemplate('foo', 'example.com', true);
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $routeBuilder = new RouteBuilder(['GET'], $expectedUriTemplate);
        $routeBuilder->mapsToMethod($controller::class, 'bar');
        $route = $routeBuilder->build();
        $this->assertSame($expectedUriTemplate, $route->uriTemplate);
    }
}
