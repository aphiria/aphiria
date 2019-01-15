<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests;

use Opulence\Routing\Matchers\Constraints\IRouteConstraint;
use Opulence\Routing\MethodRouteAction;
use Opulence\Routing\Middleware\MiddlewareBinding;
use Opulence\Routing\Route;
use Opulence\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

/**
 * Tests a route
 */
class RouteTest extends TestCase
{
    /** @var Route */
    private $route;
    /** @var UriTemplate */
    private $uriTemplate;
    /** @var MethodRouteAction */
    private $routeAction;
    /** @var IRouteConstraint[] */
    private $constraints;
    /** @var MiddlewareBinding[] */
    private $middlewareBindings;
    /** @var array */
    private $attributes;

    public function setUp(): void
    {
        $this->routeAction = new MethodRouteAction('Foo', 'bar');
        $this->uriTemplate = new UriTemplate('foo');
        $this->constraints = [$this->createMock(IRouteConstraint::class)];
        $this->middlewareBindings = [new MiddlewareBinding('Foo')];
        $this->attributes = ['foo' => 'bar'];
        $this->route = new Route(
            $this->uriTemplate,
            $this->routeAction,
            $this->constraints,
            $this->middlewareBindings,
            'name',
            $this->attributes
        );
    }

    public function testPropertiesAreSetCorrectlyInConstructor(): void
    {
        $this->assertSame($this->uriTemplate, $this->route->uriTemplate);
        $this->assertSame($this->routeAction, $this->route->action);
        $this->assertSame($this->constraints, $this->route->constraints);
        $this->assertSame($this->middlewareBindings, $this->route->middlewareBindings);
        $this->assertEquals('name', $this->route->name);
        $this->assertSame($this->attributes, $this->route->attributes);
    }
}
