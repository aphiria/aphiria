<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Matchers\Tests;

use Opulence\Routing\Matchers\Middleware\MiddlewareBinding;
use Opulence\Routing\Matchers\Route;
use Opulence\Routing\Matchers\RouteAction;
use Opulence\Routing\Matchers\UriTemplates\UriTemplate;

/**
 * Tests the routes
 */
class RouteTest extends \PHPUnit\Framework\TestCase
{
    /** @var The name of the route to use */
    private const ROUTE_NAME = 'route';
    /** @var The list of attributes to match on */
    private static $attributes = ['foo' => 'bar'];
    /** @var Route The route to use in tests */
    private $route = null;
    /** @var UriTemplate|\PHPUnit_Framework_MockObject_MockObject The URI template used by the route */
    private $uriTemplate = null;
    /** @var RouteAction|\PHPUnit_Framework_MockObject_MockObject The mocked route action */
    private $routeAction = null;
    /** @var MiddlewareBinding[] The list of middleware bindings in the matched route */
    private $middlewareBindings = [];

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->routeAction = $this->createMock(RouteAction::class);
        $this->middlewareBindings = [new MiddlewareBinding('Foo')];
        $this->uriTemplate = $this->createMock(UriTemplate::class);
        $this->route = new Route(
            ['GET'],
            $this->uriTemplate,
            $this->routeAction,
            $this->middlewareBindings,
            self::ROUTE_NAME,
            self::$attributes
        );
    }

    /**
     * Tests that the correct action is returned
     */
    public function testCorrectActionIsReturned() : void
    {
        $this->assertSame($this->routeAction, $this->route->getAction());
    }

    /**
     * Tests that the correct attributes are returned
     */
    public function testCorrectAttributesAreReturned() : void
    {
        $this->assertSame(self::$attributes, $this->route->getAttributes());
    }

    /**
     * Tests that the correct HTTP methods are returned
     */
    public function testCorrectHttpMethodsAreReturned() : void
    {
        $this->assertEquals(['GET'], $this->route->getHttpMethods());
    }

    /**
     * Tests that the correct middleware bindings are returned
     */
    public function testCorrectMiddlewareBindingsAreReturned() : void
    {
        $this->assertSame($this->middlewareBindings, $this->route->getMiddlewareBindings());
    }

    /**
     * Tests that the correct name is returned
     */
    public function testCorrectNameIsReturned() : void
    {
        $this->assertEquals(self::ROUTE_NAME, $this->route->getName());
    }

    /**
     * Tests that the correct URI template is returned
     */
    public function testCorrectUriTemplateIsReturned() : void
    {
        $this->assertSame($this->uriTemplate, $this->route->getUriTemplate());
    }

    /**
     * Tests that the name defaults to null
     */
    public function testNameDefaultsToNull() : void
    {
        $this->routeAction = $this->createMock(RouteAction::class);
        $this->middlewareBindings = [new MiddlewareBinding('Foo')];
        $this->uriTemplate = $this->createMock(UriTemplate::class);
        $namelessRoute = new Route(
            ['GET'],
            $this->uriTemplate,
            $this->routeAction,
            $this->middlewareBindings
        );
        $this->assertNull($namelessRoute->getName());
    }
}
