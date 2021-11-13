<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    private Route $route;
    private UriTemplate $uriTemplate;
    private RouteAction $routeAction;
    /** @var list<IRouteConstraint> */
    private array $constraints;
    /** @var list<MiddlewareBinding> */
    private array $middlewareBindings;
    private array $parameters;

    protected function setUp(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->routeAction = new RouteAction($controller::class, 'bar');
        $this->uriTemplate = new UriTemplate('foo');
        $this->constraints = [$this->createMock(IRouteConstraint::class)];
        $middleware = new class () {
        };
        $this->middlewareBindings = [new MiddlewareBinding($middleware::class)];
        $this->parameters = ['foo' => 'bar'];
        $this->route = new Route(
            $this->uriTemplate,
            $this->routeAction,
            $this->constraints,
            $this->middlewareBindings,
            'name',
            $this->parameters
        );
    }

    public function testPropertiesAreSetCorrectlyInConstructor(): void
    {
        $this->assertSame($this->uriTemplate, $this->route->uriTemplate);
        $this->assertSame($this->routeAction, $this->route->action);
        $this->assertSame($this->constraints, $this->route->constraints);
        $this->assertSame($this->middlewareBindings, $this->route->middlewareBindings);
        $this->assertSame('name', $this->route->name);
        $this->assertSame($this->parameters, $this->route->parameters);
    }
}
