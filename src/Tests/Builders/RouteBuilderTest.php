<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Builders;

use InvalidArgumentException;
use LogicException;
use Opulence\Routing\Builders\RouteBuilder;
use Opulence\Routing\Middleware\MiddlewareBinding;
use Opulence\Routing\UriTemplates\UriTemplate;

/**
 * Defines the tests for the route builder
 */
class RouteBuilderTest extends \PHPUnit\Framework\TestCase
{
    /** @var RouteBuilder The route builder to use in tests */
    private $routeBuilder;

    public function setUp(): void
    {
        $this->routeBuilder = new RouteBuilder(['GET'], new UriTemplate('/foo', false));
    }

    public function testBuildingRouteBeforeSettingActionThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->routeBuilder->build();
    }

    public function testChainingOnFluentMethodsReturnsCorrectInstance(): void
    {
        $this->assertSame($this->routeBuilder, $this->routeBuilder->toClosure(function () {
            // Don't do anything
        }));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->toMethod('Foo', 'bar'));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withAttribute('foo', 'bar'));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withManyAttributes(['foo' => 'bar']));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withManyMiddleware(['Foo']));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withMiddleware('Foo'));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withName('Foo'));
    }

    public function testClosureIsSetWhenUsingClosureAction(): void
    {
        $closure = function () {
            // Don't do anything
        };
        $this->routeBuilder->toClosure($closure);
        $route = $this->routeBuilder->build();
        $this->assertSame($closure, $route->getAction()->getClosure());
    }

    public function testAttributesAreSetWhenPassingIndividualAttributes(): void
    {
        $this->routeBuilder->withAttribute('foo', 'bar');
        $this->routeBuilder->toMethod('class', 'method');
        $route = $this->routeBuilder->build();
        $this->assertEquals(['foo' => 'bar'], $route->getAttributes());
    }

    public function testAttributesAreSetWhenPassingMultipleAttributes(): void
    {
        $this->routeBuilder->withManyAttributes(['foo' => 'bar']);
        $this->routeBuilder->toMethod('class', 'method');
        $route = $this->routeBuilder->build();
        $this->assertEquals(['foo' => 'bar'], $route->getAttributes());
    }

    public function testInvalidManyMiddlewareThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->routeBuilder->withManyMiddleware([1]);
    }

    public function testManyMiddlewareBindingsAreSetWhenPassingThemInAsObjects(): void
    {
        $this->routeBuilder->withManyMiddleware([
            new MiddlewareBinding('foo', ['bar' => 'baz']),
            new MiddlewareBinding('dave', ['young' => 'cool']),
        ]);
        $this->routeBuilder->toMethod('class', 'method');
        $route = $this->routeBuilder->build();
        $this->assertCount(2, $route->getMiddlewareBindings());
        $this->assertInstanceOf(MiddlewareBinding::class, $route->getMiddlewareBindings()[0]);
        $this->assertInstanceOf(MiddlewareBinding::class, $route->getMiddlewareBindings()[1]);
        $this->assertEquals('foo', $route->getMiddlewareBindings()[0]->getClassName());
        $this->assertEquals('dave', $route->getMiddlewareBindings()[1]->getClassName());
        $this->assertEquals(['bar' => 'baz'], $route->getMiddlewareBindings()[0]->getAttributes());
        $this->assertEquals(['young' => 'cool'], $route->getMiddlewareBindings()[1]->getAttributes());
    }

    public function testManyMiddlewareBindingsAreSetWhenPassingThemInAsStrings(): void
    {
        $this->routeBuilder->withManyMiddleware(['foo', 'bar']);
        $this->routeBuilder->toMethod('class', 'method');
        $route = $this->routeBuilder->build();
        $this->assertCount(2, $route->getMiddlewareBindings());
        $this->assertInstanceOf(MiddlewareBinding::class, $route->getMiddlewareBindings()[0]);
        $this->assertInstanceOf(MiddlewareBinding::class, $route->getMiddlewareBindings()[1]);
        $this->assertEquals('foo', $route->getMiddlewareBindings()[0]->getClassName());
        $this->assertEquals('bar', $route->getMiddlewareBindings()[1]->getClassName());
        $this->assertEquals([], $route->getMiddlewareBindings()[0]->getAttributes());
        $this->assertEquals([], $route->getMiddlewareBindings()[1]->getAttributes());
    }

    public function testMethodIsSetWhenUsingMethodAction(): void
    {
        $this->routeBuilder->toMethod('foo', 'bar');
        $route = $this->routeBuilder->build();
        $this->assertSame('foo', $route->getAction()->getClassName());
        $this->assertSame('bar', $route->getAction()->getMethodName());
    }

    public function testMiddlewareBindingIsSet(): void
    {
        $this->routeBuilder->withMiddleware('foo', ['bar' => 'baz']);
        $this->routeBuilder->toMethod('class', 'method');
        $route = $this->routeBuilder->build();
        $this->assertCount(1, $route->getMiddlewareBindings());
        $this->assertInstanceOf(MiddlewareBinding::class, $route->getMiddlewareBindings()[0]);
        $this->assertEquals('foo', $route->getMiddlewareBindings()[0]->getClassName());
        $this->assertEquals(['bar' => 'baz'], $route->getMiddlewareBindings()[0]->getAttributes());
    }

    public function testNameIsSet(): void
    {
        $route = $this->routeBuilder->toMethod('class', 'method')
            ->withName('foo')
            ->build();
        $this->assertEquals('foo', $route->getName());
    }
}
