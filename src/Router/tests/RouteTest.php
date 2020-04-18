<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\MethodRouteAction;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\Route;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    private Route $route;
    private UriTemplate $uriTemplate;
    private MethodRouteAction $routeAction;
    /** @var IRouteConstraint[] */
    private array $constraints;
    /** @var MiddlewareBinding[] */
    private array $middlewareBindings;
    private array $attributes;

    protected function setUp(): void
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
