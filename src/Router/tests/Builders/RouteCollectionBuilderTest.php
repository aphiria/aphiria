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

use Aphiria\Routing\Builders\RouteBuilder;
use Aphiria\Routing\Builders\RouteCollectionBuilder;
use Aphiria\Routing\Builders\RouteGroupOptions;
use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use PHPUnit\Framework\TestCase;

class RouteCollectionBuilderTest extends TestCase
{
    private RouteCollectionBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new RouteCollectionBuilder();
    }

    public function testBuildingWithNoRoutesReturnsEmptyArray(): void
    {
        $this->assertEmpty($this->builder->build()->getAll());
    }

    public function testGroupAttributesToMatchOnAreMergedWithRouteAttributesToMatch(): void
    {
        $groupOptions = new RouteGroupOptions('foo', null, false, [], [], ['H1' => 'val1']);
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) {
            $registry->route('GET', '')
                ->mapsToMethod('foo', 'bar')
                ->withAttribute('H2', 'val2');
        });
        $routes = $this->builder->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals(['H1' => 'val1', 'H2' => 'val2'], $routes[0]->attributes);
    }

    public function testGroupConstraintsAreMergedWithRouteAttributes(): void
    {
        $groupConstraints = [$this->createMock(IRouteConstraint::class)];
        $groupOptions = new RouteGroupOptions('foo', null, false, $groupConstraints);
        $routeConstraints = [$this->createMock(IRouteConstraint::class)];
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) use ($routeConstraints) {
            $registry->route('GET', '')
                ->mapsToMethod('foo', 'bar')
                ->withManyConstraints($routeConstraints);
        });
        $routes = $this->builder->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertContains($groupConstraints[0], $routes[0]->constraints);
        $this->assertContains($routeConstraints[0], $routes[0]->constraints);
    }

    public function testGroupingAppendsToRouteHostTemplate(): void
    {
        $groupOptions = new RouteGroupOptions('foo', 'baz', false);
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) {
            $registry->route('GET', '', 'bar')
                ->mapsToMethod('controller', 'method');
        });
        $routes = $this->builder->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('bar.baz', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupOptionsDoNotApplyToRoutesAddedOutsideGroup(): void
    {
        $groupOptions = new RouteGroupOptions('gp');
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) {
            $registry->route('GET', 'rp1')
                ->mapsToMethod('c1', 'm1');
        });
        $this->builder->route('POST', 'rp2')
            ->mapsToMethod('c2', 'm2');
        $routes = $this->builder->build()->getAll();
        $this->assertCount(2, $routes);
        $this->assertEquals('/gp/rp1', $routes[0]->uriTemplate->pathTemplate);
        $this->assertEquals('/rp2', $routes[1]->uriTemplate->pathTemplate);
    }

    public function testGroupMiddlewareAreMergedWithRouteMiddleware(): void
    {
        $groupMiddlewareBinding = new MiddlewareBinding('foo');
        $routeMiddlewareBinding = new MiddlewareBinding('bar');
        $groupOptions = new RouteGroupOptions('', null, false, [], [$groupMiddlewareBinding], []);
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) use ($routeMiddlewareBinding) {
            // Use the bulk-with method so we can pass in an already-instantiated object to check against later
            $registry->route('GET', '')
                ->mapsToMethod('foo', 'bar')
                ->withManyMiddleware([$routeMiddlewareBinding]);
        });
        $routes = $this->builder->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals([$groupMiddlewareBinding, $routeMiddlewareBinding], $routes[0]->middlewareBindings);
    }

    public function testGroupHostWithNestedGroupHostHasDotBetweenThem(): void
    {
        $registry = new RouteCollectionBuilder();
        $registry->group(new RouteGroupOptions('', 'example.com'), function (RouteCollectionBuilder $registry) {
            $registry->group(new RouteGroupOptions('', 'foo'), function (RouteCollectionBuilder $registry) {
                $registry->route('GET', 'baz', 'bar')
                    ->mapsToMethod('c1', 'm1');
            });
        });
        $routes = $registry->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('bar.foo.example.com', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupHostWithNoDotAndRouteHostWithTrailingDotHasDotBetweenThem(): void
    {
        $registry = new RouteCollectionBuilder();
        $registry->group(new RouteGroupOptions('', 'example.com'), function (RouteCollectionBuilder $registry) {
            $registry->route('GET', '', 'foo.')
                ->mapsToMethod('c1', 'm1');
        });
        $routes = $registry->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('foo.example.com', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupHostWithNoDotAndRouteHostWithNoDosHasDotBetweenThem(): void
    {
        $registry = new RouteCollectionBuilder();
        $registry->group(new RouteGroupOptions('', 'example.com'), function (RouteCollectionBuilder $registry) {
            $registry->route('GET', '', 'foo')
                ->mapsToMethod('c1', 'm1');
        });
        $routes = $registry->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('foo.example.com', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupPathWithNoSlashAndRoutePathWithLeadingSlashHaveSlashBetweenThem(): void
    {
        $registry = new RouteCollectionBuilder();
        $registry->group(new RouteGroupOptions('foo'), function (RouteCollectionBuilder $registry) {
            $registry->route('GET', '/bar')
                ->mapsToMethod('c1', 'm1');
        });
        $routes = $registry->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo/bar', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testGroupPathWithNoSlashAndRoutePathWithNoSlashHaveSlashBetweenThem(): void
    {
        $registry = new RouteCollectionBuilder();
        $registry->group(new RouteGroupOptions('foo'), function (RouteCollectionBuilder $registry) {
            $registry->route('GET', 'bar')
                ->mapsToMethod('c1', 'm1');
        });
        $routes = $registry->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo/bar', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testHttpsOnlyGroupOverridesHttpsSettingInRoutes(): void
    {
        $this->builder->group(new RouteGroupOptions('', null, true), function (RouteCollectionBuilder $registry) {
            $registry->route('GET', '', null, false)
                ->mapsToMethod('foo', 'bar');
        });
        $routes = $this->builder->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertTrue($routes[0]->uriTemplate->isHttpsOnly);
    }

    public function testNestedGroupOptionsAreAddedCorrectlyToRoute(): void
    {
        $outerConstraints = [$this->createMock(IRouteConstraint::class)];
        $outerGroupMiddlewareBinding = new MiddlewareBinding('foo');
        $innerConstraints = [$this->createMock(IRouteConstraint::class)];
        $innerGroupMiddlewareBinding = new MiddlewareBinding('bar');
        $routeMiddlewareBinding = new MiddlewareBinding('baz');
        $outerGroupOptions = new RouteGroupOptions(
            'op',
            null,
            false,
            $outerConstraints,
            [$outerGroupMiddlewareBinding]
        );
        $this->builder->group(
            $outerGroupOptions,
            function (RouteCollectionBuilder $registry) use (
                $innerConstraints,
                $innerGroupMiddlewareBinding,
                $routeMiddlewareBinding
            ) {
                $innerGroupOptions = new RouteGroupOptions(
                    'ip',
                    null,
                    false,
                    $innerConstraints,
                    [$innerGroupMiddlewareBinding]
                );
                $registry->group(
                    $innerGroupOptions,
                    function (RouteCollectionBuilder $registry) use ($routeMiddlewareBinding) {
                        // Use the bulk-with method so we can pass in an already-instantiated object to check against later
                        $registry->route('GET', 'rp')
                            ->mapsToMethod('foo', 'bar')
                            ->withManyMiddleware([$routeMiddlewareBinding]);
                    }
                );
            }
        );
        $routes = $this->builder->build()->getAll();
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
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) {
            $registry->route('GET', 'bar')
                ->mapsToMethod('controller', 'method');
        });
        $routes = $this->builder->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo/bar', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testRouteAddsLeadingSlashToPath(): void
    {
        $this->builder->route('GET', 'foo')
            ->mapsToMethod('Foo', 'bar');
        $routes = $this->builder->build()->getAll();
        $this->assertCount(1, $routes);
        $this->assertEquals('/foo', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testRouteBuilderIsCreatedWithAttributesToMatchParameter(): void
    {
        $routeBuilder = $this->builder->route('GET', '')
            ->mapsToMethod('foo', 'bar')
            ->withAttribute('FOO', 'BAR');
        $route = $routeBuilder->build();
        $this->assertEquals(['FOO' => 'BAR'], $route->attributes);
    }

    public function testRouteBuilderIsCreatedWithConstraints(): void
    {
        $constraints = [$this->createMock(IRouteConstraint::class)];
        $routeBuilder = $this->builder->route('GET', '')
            ->mapsToMethod('foo', 'bar')
            ->withManyConstraints($constraints);
        $route = $routeBuilder->build();
        $this->assertContains($constraints[0], $route->constraints);
    }

    public function testRouteBuilderIsCreatedWithHttpMethodParameterSet(): void
    {
        $routeBuilder = $this->builder->route(['GET', 'DELETE'], '')
            ->mapsToMethod('foo', 'bar');
        $route = $routeBuilder->build();
        $this->assertCount(1, $route->constraints);
        /** @var HttpMethodRouteConstraint $httpMethodRouteConstraint */
        $httpMethodRouteConstraint = $route->constraints[0];
        $this->assertInstanceOf(HttpMethodRouteConstraint::class, $httpMethodRouteConstraint);
        // HEAD is automatically inserted for GET routes
        $this->assertEquals(['GET', 'DELETE', 'HEAD'], $httpMethodRouteConstraint->getAllowedMethods());
    }

    public function testRouteConvenienceMethodsCreateRoutesWithProperMethods(): void
    {
        foreach (['DELETE', 'GET', 'OPTIONS', 'PATCH', 'POST', 'PUT'] as $httpMethod) {
            /** @var RouteBuilder $routeBuilder */
            $routeBuilder = $this->builder->{\strtolower($httpMethod)}('foo');
            $routeBuilder->mapsToMethod('Foo', 'bar');
            $route = $routeBuilder->build();
            $this->assertCount(1, $route->constraints);
            /** @var HttpMethodRouteConstraint $methodConstraint */
            $methodConstraint = $route->constraints[0];
            $this->assertInstanceOf(HttpMethodRouteConstraint::class, $methodConstraint);
            /**
             * Specifically checking contains as opposed to equals because some constraints, eg GET, might contain
             * additional methods, eg HEAD
             */
            $this->assertContains($httpMethod, $methodConstraint->getAllowedMethods());
        }
    }
}
