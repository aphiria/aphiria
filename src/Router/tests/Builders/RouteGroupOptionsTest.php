<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Builders;

use Aphiria\Routing\Builders\RouteGroupOptions;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use PHPUnit\Framework\TestCase;

class RouteGroupOptionsTest extends TestCase
{
    private RouteGroupOptions $routeGroupOptions;
    /** @var IRouteConstraint[] The list of constraints */
    private array $constraints;
    /** @var MiddlewareBinding[] The list of middleware bindings in the options */
    private array $middlewareBindings = [];

    protected function setUp(): void
    {
        $this->constraints = [$this->createMock(IRouteConstraint::class)];
        $this->middlewareBindings = [new MiddlewareBinding('foo')];
        $this->routeGroupOptions = new RouteGroupOptions(
            'path',
            'host',
            true,
            $this->constraints,
            $this->middlewareBindings,
            ['foo' => 'bar']
        );
    }

    public function testCorrectAttributesAreReturned(): void
    {
        $this->assertEquals(['foo' => 'bar'], $this->routeGroupOptions->attributes);
    }

    public function testCorrectConstraintsAreReturned(): void
    {
        $this->assertEquals($this->constraints, $this->routeGroupOptions->constraints);
    }

    public function testCorrectHostIsReturned(): void
    {
        $this->assertSame('host', $this->routeGroupOptions->hostTemplate);
    }

    public function testCorrectHttpsOnlyIsReturned(): void
    {
        $this->assertTrue($this->routeGroupOptions->isHttpsOnly);
    }

    public function testCorrectMiddlewareBindingsAreReturned(): void
    {
        $this->assertEquals($this->middlewareBindings, $this->routeGroupOptions->middlewareBindings);
    }

    public function testCorrectPathIsReturned(): void
    {
        $this->assertSame('path', $this->routeGroupOptions->pathTemplate);
    }
}
