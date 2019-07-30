<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/router/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests\Builders;

use Aphiria\Routing\Builders\RouteBuilderRegistry;
use Aphiria\Routing\Builders\RouteGroupOptions;
use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use PHPUnit\Framework\TestCase;

/**
 * Tests the route builder registry
 */
class RouteBuilderRegistryTest extends TestCase
{
    /** @var RouteBuilderRegistry The registry to use in tests */
    private RouteBuilderRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new RouteBuilderRegistry();
    }

    public function testBuildingWithNoRoutesReturnsEmptyArray(): void
    {
        $this->assertEmpty($this->registry->buildAll());
    }

    public function testGroupAttributesToMatchOnAreMergedWithRouteAttributesToMatch(): void
    {
        $groupOptions = new RouteGroupOptions('foo', null, false, [], [], ['H1' => 'val1']);
        $this->registry->group($groupOptions, function (RouteBuilderRegistry $registry) {
            $registry->map('GET', '')
                ->toMethod('foo', 'bar')
                ->withAttribute('H2', 'val2');
        });
        $routes = $this->registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertEquals(['H1' => 'val1', 'H2' => 'val2'], $routes[0]->attributes);
    }

    public function testGroupConstraintsAreMergedWithRouteAttributes(): void
    {
        $groupConstraints = [$this->createMock(IRouteConstraint::class)];
        $groupOptions = new RouteGroupOptions('foo', null, false, $groupConstraints);
        $routeConstraints = [$this->createMock(IRouteConstraint::class)];
        $this->registry->group($groupOptions, function (RouteBuilderRegistry $registry) use ($routeConstraints) {
            $registry->map('GET', '')
                ->toMethod('foo', 'bar')
                ->withManyConstraints($routeConstraints);
        });
        $routes = $this->registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertContains($groupConstraints[0], $routes[0]->constraints);
        $this->assertContains($routeConstraints[0], $routes[0]->constraints);
    }

    public function testGroupingAppendsToRouteHostTemplate(): void
    {
        $groupOptions = new RouteGroupOptions('foo', 'baz', false);
        $this->registry->group($groupOptions, function (RouteBuilderRegistry $registry) {
            $registry->map('GET', '', 'bar')
                ->toMethod('controller', 'method');
        });
        $routes = $this->registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('bar.baz', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupOptionsDoNotApplyToRoutesAddedOutsideGroup(): void
    {
        $groupOptions = new RouteGroupOptions('gp');
        $this->registry->group($groupOptions, function (RouteBuilderRegistry $registry) {
            $registry->map('GET', 'rp1')
                ->toMethod('c1', 'm1');
        });
        $this->registry->map('POST', 'rp2')
            ->toMethod('c2', 'm2');
        $routes = $this->registry->buildAll();
        $this->assertCount(2, $routes);
        $this->assertEquals('/gp/rp1', $routes[0]->uriTemplate->pathTemplate);
        $this->assertEquals('/rp2', $routes[1]->uriTemplate->pathTemplate);
    }

    public function testGroupMiddlewareAreMergedWithRouteMiddleware(): void
    {
        $groupMiddlewareBinding = new MiddlewareBinding('foo');
        $routeMiddlewareBinding = new MiddlewareBinding('bar');
        $groupOptions = new RouteGroupOptions('', null, false, [], [$groupMiddlewareBinding], []);
        $this->registry->group($groupOptions, function (RouteBuilderRegistry $registry) use ($routeMiddlewareBinding) {
            // Use the bulk-with method so we can pass in an already-instantiated object to check against later
            $registry->map('GET', '')
                ->toMethod('foo', 'bar')
                ->withManyMiddleware([$routeMiddlewareBinding]);
        });
        $routes = $this->registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertEquals([$groupMiddlewareBinding, $routeMiddlewareBinding], $routes[0]->middlewareBindings);
    }

    public function testGroupHostWithNestedGroupHostHasDotBetweenThem(): void
    {
        $registry = new RouteBuilderRegistry();
        $registry->group(new RouteGroupOptions('', 'example.com'), function (RouteBuilderRegistry $registry) {
            $registry->group(new RouteGroupOptions('', 'foo'), function (RouteBuilderRegistry $registry) {
                $registry->map('GET', 'baz', 'bar')
                    ->toMethod('c1', 'm1');
            });
        });
        $routes = $registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('bar.foo.example.com', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupHostWithNoDotAndRouteHostWithTrailingDotHasDotBetweenThem(): void
    {
        $registry = new RouteBuilderRegistry();
        $registry->group(new RouteGroupOptions('', 'example.com'), function (RouteBuilderRegistry $registry) {
            $registry->map('GET', '', 'foo.')
                ->toMethod('c1', 'm1');
        });
        $routes = $registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('foo.example.com', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupHostWithNoDotAndRouteHostWithNoDosHasDotBetweenThem(): void
    {
        $registry = new RouteBuilderRegistry();
        $registry->group(new RouteGroupOptions('', 'example.com'), function (RouteBuilderRegistry $registry) {
            $registry->map('GET', '', 'foo')
                ->toMethod('c1', 'm1');
        });
        $routes = $registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('foo.example.com', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupPathWithNoSlashAndRoutePathWithLeadingSlashHaveSlashBetweenThem(): void
    {
        $registry = new RouteBuilderRegistry();
        $registry->group(new RouteGroupOptions('foo'), function (RouteBuilderRegistry $registry) {
            $registry->map('GET', '/bar')
                ->toMethod('c1', 'm1');
        });
        $routes = $registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo/bar', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testGroupPathWithNoSlashAndRoutePathWithNoSlashHaveSlashBetweenThem(): void
    {
        $registry = new RouteBuilderRegistry();
        $registry->group(new RouteGroupOptions('foo'), function (RouteBuilderRegistry $registry) {
            $registry->map('GET', 'bar')
                ->toMethod('c1', 'm1');
        });
        $routes = $registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo/bar', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testHttpsOnlyGroupOverridesHttpsSettingInRoutes(): void
    {
        $this->registry->group(new RouteGroupOptions('', null, true), function (RouteBuilderRegistry $registry) {
            $registry->map('GET', '', null, false)
                ->toMethod('foo', 'bar');
        });
        $routes = $this->registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertTrue($routes[0]->uriTemplate->isHttpsOnly);
    }

    public function testMapAddsLeadingSlashToPath(): void
    {
        $this->registry->map('GET', 'foo')
            ->toMethod('Foo', 'bar');
        $routes = $this->registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testNestedGroupOptionsAreAddedCorrectlyToRoute(): void
    {
        $outerConstraints = [$this->createMock(IRouteConstraint::class)];
        $outerGroupMiddlewareBinding = new MiddlewareBinding('foo');
        $innerConstraints = [$this->createMock(IRouteConstraint::class)];
        $innerGroupMiddlewareBinding = new MiddlewareBinding('bar');
        $routeMiddlewareBinding = new MiddlewareBinding('baz');
        $outerGroupOptions = new RouteGroupOptions('op', null, false, $outerConstraints,
            [$outerGroupMiddlewareBinding]);
        $this->registry->group(
            $outerGroupOptions,
            function (RouteBuilderRegistry $registry) use (
                $innerConstraints,
                $innerGroupMiddlewareBinding,
                $routeMiddlewareBinding
            ) {
                $innerGroupOptions = new RouteGroupOptions('ip', null, false, $innerConstraints,
                    [$innerGroupMiddlewareBinding]);
                $registry->group(
                    $innerGroupOptions,
                    function (RouteBuilderRegistry $registry) use ($routeMiddlewareBinding) {
                        // Use the bulk-with method so we can pass in an already-instantiated object to check against later
                        $registry->map('GET', 'rp')
                            ->toMethod('foo', 'bar')
                            ->withManyMiddleware([$routeMiddlewareBinding]);
                    }
                );
            }
        );
        $routes = $this->registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/op/ip/rp', $routes[0]->uriTemplate->pathTemplate);
        $this->assertContains($outerConstraints[0], $routes[0]->constraints);
        $this->assertContains($innerConstraints[0], $routes[0]->constraints);
        $expectedMiddlewareBindings = [
            $outerGroupMiddlewareBinding,
            $innerGroupMiddlewareBinding,
            $routeMiddlewareBinding
        ];
        $this->assertEquals($expectedMiddlewareBindings, $routes[0]->middlewareBindings);
    }

    public function testGroupingPrependsToRoutePathTemplate(): void
    {
        $groupOptions = new RouteGroupOptions('foo');
        $this->registry->group($groupOptions, function (RouteBuilderRegistry $registry) {
            $registry->map('GET', 'bar')
                ->toMethod('controller', 'method');
        });
        $routes = $this->registry->buildAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo/bar', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testRouteBuilderIsCreatedWithAttributesToMatchParameter(): void
    {
        $routeBuilder = $this->registry->map('GET', '')
            ->toMethod('foo', 'bar')
            ->withAttribute('FOO', 'BAR');
        $route = $routeBuilder->build();
        $this->assertEquals(['FOO' => 'BAR'], $route->attributes);
    }

    public function testRouteBuilderIsCreatedWithConstraints(): void
    {
        $constraints = [$this->createMock(IRouteConstraint::class)];
        $routeBuilder = $this->registry->map('GET', '')
            ->toMethod('foo', 'bar')
            ->withManyConstraints($constraints);
        $route = $routeBuilder->build();
        $this->assertContains($constraints[0], $route->constraints);
    }

    public function testRouteBuilderIsCreatedWithHttpMethodParameterSet(): void
    {
        $routeBuilder = $this->registry->map(['GET', 'DELETE'], '')
            ->toMethod('foo', 'bar');
        $route = $routeBuilder->build();
        $this->assertCount(1, $route->constraints);
        /** @var HttpMethodRouteConstraint $httpMethodRouteConstraint */
        $httpMethodRouteConstraint = $route->constraints[0];
        $this->assertInstanceOf(HttpMethodRouteConstraint::class, $httpMethodRouteConstraint);
        // HEAD is automatically inserted for GET routes
        $this->assertEquals(['GET', 'DELETE', 'HEAD'], $httpMethodRouteConstraint->getAllowedMethods());
    }
}
