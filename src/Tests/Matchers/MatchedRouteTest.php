<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Matchers;

use Opulence\Routing\Matchers\MatchedRoute;
use Opulence\Routing\Middleware\MiddlewareBinding;
use Opulence\Routing\RouteAction;

/**
 * Tests the matched routes
 */
class MatchedRouteTest extends \PHPUnit\Framework\TestCase
{
    /** @var MatchedRoute The matched route to use in tests */
    private $matchedRoute;
    /** @var RouteAction|\PHPUnit_Framework_MockObject_MockObject The mocked route action */
    private $routeAction;
    /** @var MiddlewareBinding[] The list of middleware bindings in the matched route */
    private $middlewareBindings = [];
    /** @var array The route vars in the matched route */
    private $routeVars = [];

    public function setUp(): void
    {
        $this->routeAction = $this->createMock(RouteAction::class);
        $this->middlewareBindings = [new MiddlewareBinding('Foo')];
        $this->routeVars = ['foo' => 'bar'];
        $this->matchedRoute = new MatchedRoute($this->routeAction, $this->routeVars, $this->middlewareBindings);
    }

    public function testCorrectActionIsReturned(): void
    {
        $this->assertSame($this->routeAction, $this->matchedRoute->getAction());
    }

    public function testCorrectMiddlewareBindingsAreReturned(): void
    {
        $this->assertSame($this->middlewareBindings, $this->matchedRoute->getMiddlewareBindings());
    }

    public function testCorrectRouteVarsAreReturned(): void
    {
        $this->assertSame($this->routeVars, $this->matchedRoute->getRouteVars());
    }
}
