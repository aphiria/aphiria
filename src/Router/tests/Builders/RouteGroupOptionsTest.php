<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Builders;

use Aphiria\Routing\Builders\RouteGroupOptions;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use PHPUnit\Framework\TestCase;

class RouteGroupOptionsTest extends TestCase
{
    /** @var list<IRouteConstraint> The list of constraints */
    private array $constraints;
    /** @var list<MiddlewareBinding> The list of middleware bindings in the options */
    private array $middlewareBindings = [];
    private RouteGroupOptions $routeGroupOptions;

    protected function setUp(): void
    {
        $this->constraints = [$this->createMock(IRouteConstraint::class)];
        $middleware = new class () {
        };
        $this->middlewareBindings = [new MiddlewareBinding($middleware::class)];
        $this->routeGroupOptions = new RouteGroupOptions(
            'path',
            'host',
            true,
            $this->constraints,
            $this->middlewareBindings,
            ['foo' => 'bar']
        );
    }

    public function testCorrectConstraintsAreReturned(): void
    {
        $this->assertEquals($this->constraints, $this->routeGroupOptions->constraints);
    }

    public function testCorrectHostIsReturned(): void
    {
        $this->assertSame('host', $this->routeGroupOptions->host);
    }

    public function testCorrectHttpsOnlyIsReturned(): void
    {
        $this->assertTrue($this->routeGroupOptions->isHttpsOnly);
    }

    public function testCorrectMiddlewareBindingsAreReturned(): void
    {
        $this->assertEquals($this->middlewareBindings, $this->routeGroupOptions->middlewareBindings);
    }

    public function testCorrectParametersAreReturned(): void
    {
        $this->assertEquals(['foo' => 'bar'], $this->routeGroupOptions->parameters);
    }

    public function testCorrectPathIsReturned(): void
    {
        $this->assertSame('path', $this->routeGroupOptions->path);
    }
}
