<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests;

use Opulence\Routing\Middleware\MiddlewareBinding;
use Opulence\Routing\Route;
use Opulence\Routing\RouteAction;
use Opulence\Routing\UriTemplates\UriTemplate;

/**
 * Tests the routes
 */
class RouteTest extends \PHPUnit\Framework\TestCase
{
    /** @const The name of the route to use */
    private const ROUTE_NAME = 'route';
    /** @var array The list of attributes to match on */
    private static $attributes = ['foo' => 'bar'];
    /** @var Route The route to use in tests */
    private $route;
    /** @var UriTemplate|\PHPUnit_Framework_MockObject_MockObject The URI template used by the route */
    private $uriTemplate;
    /** @var RouteAction|\PHPUnit_Framework_MockObject_MockObject The mocked route action */
    private $routeAction;
    /** @var MiddlewareBinding[] The list of middleware bindings in the matched route */
    private $middlewareBindings = [];

    public function setUp(): void
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

    public function testCorrectActionIsReturned(): void
    {
        $this->assertSame($this->routeAction, $this->route->getAction());
    }

    public function testCorrectAttributesAreReturned(): void
    {
        $this->assertSame(self::$attributes, $this->route->getAttributes());
    }

    public function testCorrectHttpMethodsAreReturned(): void
    {
        $this->assertEquals(['GET'], $this->route->getHttpMethods());
    }

    public function testCorrectMiddlewareBindingsAreReturned(): void
    {
        $this->assertSame($this->middlewareBindings, $this->route->getMiddlewareBindings());
    }

    public function testCorrectNameIsReturned(): void
    {
        $this->assertEquals(self::ROUTE_NAME, $this->route->getName());
    }

    public function testCorrectUriTemplateIsReturned(): void
    {
        $this->assertSame($this->uriTemplate, $this->route->getUriTemplate());
    }

    public function testNameDefaultsToNull(): void
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
