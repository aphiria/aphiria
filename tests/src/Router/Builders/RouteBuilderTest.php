<?php
namespace Opulence\Router\Builders;

use InvalidArgumentException;
use LogicException;
use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\UriTemplates\RegexUriTemplate;

/**
 * Defines the tests for the route builder
 */
class RouteBuilderTest extends \PHPUnit\Framework\TestCase
{
    /** @var RouteBuilder The route builder to use in tests */
    private $routeBuilder = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->routeBuilder = new RouteBuilder(['GET'], new RegexUriTemplate('/foo'), false);
    }

    /**
     * Tests that building the route before setting an action throws an exception
     */
    public function testBuildingRouteBeforeSettingActionThrowsException() : void
    {
        $this->expectException(LogicException::class);
        $this->routeBuilder->build();
    }

    /**
     * Tests that chaining on fluent methods returns the correct instance
     */
    public function testChainingOnFluentMethodsReturnsCorrectInstance() : void
    {
        $this->assertSame($this->routeBuilder, $this->routeBuilder->toClosure(function () {
            // Don't do anything
        }));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->toMethod('Foo', 'bar'));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withManyMiddleware(['Foo']));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withMiddleware('Foo'));
        $this->assertSame($this->routeBuilder, $this->routeBuilder->withName('Foo'));
    }

    /**
     * Tests that the closure is set when using a closure action
     */
    public function testClosureIsSetWhenUsingClosureAction() : void
    {
        $closure = function () {
            // Don't do anything
        };
        $this->routeBuilder->toClosure($closure);
        $route = $this->routeBuilder->build();
        $this->assertSame($closure, $route->getAction()->getClosure());
    }

    /**
     * Tests that an invalid middleware throws an exception
     */
    public function testInvalidManyMiddlewareThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->routeBuilder->withManyMiddleware([1]);
    }

    /**
     * Tests that many middleware bindings are set when passing them in as objects
     */
    public function testManyMiddlewareBindingsAreSetWhenPassingThemInAsObjects() : void
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
        $this->assertEquals(['bar' => 'baz'], $route->getMiddlewareBindings()[0]->getProperties());
        $this->assertEquals(['young' => 'cool'], $route->getMiddlewareBindings()[1]->getProperties());
    }

    /**
     * Tests that many middleware bindings are set when passing them in as strings
     */
    public function testManyMiddlewareBindingsAreSetWhenPassingThemInAsStrings() : void
    {
        $this->routeBuilder->withManyMiddleware(['foo', 'bar']);
        $this->routeBuilder->toMethod('class', 'method');
        $route = $this->routeBuilder->build();
        $this->assertCount(2, $route->getMiddlewareBindings());
        $this->assertInstanceOf(MiddlewareBinding::class, $route->getMiddlewareBindings()[0]);
        $this->assertInstanceOf(MiddlewareBinding::class, $route->getMiddlewareBindings()[1]);
        $this->assertEquals('foo', $route->getMiddlewareBindings()[0]->getClassName());
        $this->assertEquals('bar', $route->getMiddlewareBindings()[1]->getClassName());
        $this->assertEquals([], $route->getMiddlewareBindings()[0]->getProperties());
        $this->assertEquals([], $route->getMiddlewareBindings()[1]->getProperties());
    }

    /**
     * Tests that the method is set when using a method action
     */
    public function testMethodIsSetWhenUsingMethodAction() : void
    {
        $this->routeBuilder->toMethod('foo', 'bar');
        $route = $this->routeBuilder->build();
        $this->assertSame('foo', $route->getAction()->getClassName());
        $this->assertSame('bar', $route->getAction()->getMethodName());
    }

    /**
     * Tests that a middleware binding is set
     */
    public function testMiddlewareBindingIsSet() : void
    {
        $this->routeBuilder->withMiddleware('foo', ['bar' => 'baz']);
        $this->routeBuilder->toMethod('class', 'method');
        $route = $this->routeBuilder->build();
        $this->assertCount(1, $route->getMiddlewareBindings());
        $this->assertInstanceOf(MiddlewareBinding::class, $route->getMiddlewareBindings()[0]);
        $this->assertEquals('foo', $route->getMiddlewareBindings()[0]->getClassName());
        $this->assertEquals(['bar' => 'baz'], $route->getMiddlewareBindings()[0]->getProperties());
    }

    /**
     * Tests that the name is set on the route
     */
    public function testNameIsSet() : void
    {
        $route = $this->routeBuilder->toMethod('class', 'method')
            ->withName('foo')
            ->build();
        $this->assertEquals('foo', $route->getName());
    }
}
