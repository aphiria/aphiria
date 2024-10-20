<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Routing\Tests;

use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\Matchers\Constraints\IRouteConstraint;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\RouteBuilder;
use Aphiria\Routing\RouteCollectionBuilder;
use Aphiria\Routing\RouteGroupOptions;
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
        $this->assertEmpty($this->builder->build()->values);
    }

    public function testGroupConstraintsAreMergedWithRouteParameters(): void
    {
        $groupConstraints = [$this->createMock(IRouteConstraint::class)];
        $groupOptions = new RouteGroupOptions('foo', null, false, $groupConstraints);
        $routeConstraints = [$this->createMock(IRouteConstraint::class)];
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) use ($routeConstraints) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $registry->route('GET', '')
                ->mapsToMethod($controller::class, 'bar')
                ->withManyConstraints($routeConstraints);
        });
        $routes = $this->builder->build()->values;
        $this->assertCount(1, $routes);
        $this->assertContains($groupConstraints[0], $routes[0]->constraints);
        $this->assertContains($routeConstraints[0], $routes[0]->constraints);
    }

    public function testGroupHostWithNestedGroupHostHasDotBetweenThem(): void
    {
        $registry = new RouteCollectionBuilder();
        $registry->group(new RouteGroupOptions('', 'example.com'), function (RouteCollectionBuilder $registry) {
            $registry->group(new RouteGroupOptions('', 'foo'), function (RouteCollectionBuilder $registry) {
                $controller = new class () {
                    public function bar(): void
                    {
                    }
                };
                $registry->route('GET', 'baz', 'bar')
                    ->mapsToMethod($controller::class, 'bar');
            });
        });
        $routes = $registry->build()->values;
        $this->assertCount(1, $routes);
        $this->assertSame('bar.foo.example.com', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupHostWithNoDotAndRouteHostWithNoDosHasDotBetweenThem(): void
    {
        $registry = new RouteCollectionBuilder();
        $registry->group(new RouteGroupOptions('', 'example.com'), function (RouteCollectionBuilder $registry) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $registry->route('GET', '', 'foo')
                ->mapsToMethod($controller::class, 'bar');
        });
        $routes = $registry->build()->values;
        $this->assertCount(1, $routes);
        $this->assertSame('foo.example.com', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupHostWithNoDotAndRouteHostWithTrailingDotHasDotBetweenThem(): void
    {
        $registry = new RouteCollectionBuilder();
        $registry->group(new RouteGroupOptions('', 'example.com'), function (RouteCollectionBuilder $registry) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $registry->route('GET', '', 'foo.')
                ->mapsToMethod($controller::class, 'bar');
        });
        $routes = $registry->build()->values;
        $this->assertCount(1, $routes);
        $this->assertSame('foo.example.com', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupingAppendsToRouteHostTemplate(): void
    {
        $groupOptions = new RouteGroupOptions('foo', 'baz', false);
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $registry->route('GET', '', 'bar')
                ->mapsToMethod($controller::class, 'bar');
        });
        $routes = $this->builder->build()->values;
        $this->assertCount(1, $routes);
        $this->assertSame('bar.baz', $routes[0]->uriTemplate->hostTemplate);
    }

    public function testGroupingPrependsToRoutePathTemplate(): void
    {
        $groupOptions = new RouteGroupOptions('foo');
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $registry->route('GET', 'bar')
                ->mapsToMethod($controller::class, 'bar');
        });
        $routes = $this->builder->build()->values;
        $this->assertCount(1, $routes);
        $this->assertSame('/foo/bar', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testGroupMiddlewareAreMergedWithRouteMiddleware(): void
    {
        $middleware1 = new class () {
        };
        $middleware2 = new class () {
        };
        $groupMiddlewareBinding = new MiddlewareBinding($middleware1::class);
        $routeMiddlewareBinding = new MiddlewareBinding($middleware2::class);
        $groupOptions = new RouteGroupOptions('', null, false, [], [$groupMiddlewareBinding], []);
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) use ($routeMiddlewareBinding) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            // Use the bulk-with method so we can pass in an already-instantiated object to check against later
            $registry->route('GET', '')
                ->mapsToMethod($controller::class, 'bar')
                ->withManyMiddleware([$routeMiddlewareBinding]);
        });
        $routes = $this->builder->build()->values;
        $this->assertCount(1, $routes);
        $this->assertEquals([$groupMiddlewareBinding, $routeMiddlewareBinding], $routes[0]->middlewareBindings);
    }

    public function testGroupOptionsDoNotApplyToRoutesAddedOutsideGroup(): void
    {
        $groupOptions = new RouteGroupOptions('gp');
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $registry->route('GET', 'rp1')
                ->mapsToMethod($controller::class, 'bar');
        });
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->builder->route('POST', 'rp2')
            ->mapsToMethod($controller::class, 'bar');
        $routes = $this->builder->build()->values;
        $this->assertCount(2, $routes);
        $this->assertSame('/gp/rp1', $routes[0]->uriTemplate->pathTemplate);
        $this->assertSame('/rp2', $routes[1]->uriTemplate->pathTemplate);
    }

    public function testGroupParametersToMatchOnAreMergedWithRouteParametersToMatch(): void
    {
        $groupOptions = new RouteGroupOptions('foo', null, false, [], [], ['H1' => 'val1']);
        $this->builder->group($groupOptions, function (RouteCollectionBuilder $registry) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $registry->route('GET', '')
                ->mapsToMethod($controller::class, 'bar')
                ->withParameter('H2', 'val2');
        });
        $routes = $this->builder->build()->values;
        $this->assertCount(1, $routes);
        $this->assertEquals(['H1' => 'val1', 'H2' => 'val2'], $routes[0]->parameters);
    }

    public function testGroupPathWithEmptyPathForRouteDoesNotAppendSlashToEnd(): void
    {
        $registry = new RouteCollectionBuilder();
        $registry->group(new RouteGroupOptions('foo'), function (RouteCollectionBuilder $registry) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $registry->route('GET', '')
                ->mapsToMethod($controller::class, 'bar');
        });
        $routes = $registry->build()->values;
        $this->assertCount(1, $routes);
        $this->assertSame('/foo', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testGroupPathWithNoSlashAndRoutePathWithLeadingSlashHaveSlashBetweenThem(): void
    {
        $registry = new RouteCollectionBuilder();
        $registry->group(new RouteGroupOptions('foo'), function (RouteCollectionBuilder $registry) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $registry->route('GET', '/bar')
                ->mapsToMethod($controller::class, 'bar');
        });
        $routes = $registry->build()->values;
        $this->assertCount(1, $routes);
        $this->assertSame('/foo/bar', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testGroupPathWithNoSlashAndRoutePathWithNoSlashHaveSlashBetweenThem(): void
    {
        $registry = new RouteCollectionBuilder();
        $registry->group(new RouteGroupOptions('foo'), function (RouteCollectionBuilder $registry) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $registry->route('GET', 'bar')
                ->mapsToMethod($controller::class, 'bar');
        });
        $routes = $registry->build()->values;
        $this->assertCount(1, $routes);
        $this->assertSame('/foo/bar', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testHttpsOnlyGroupOverridesHttpsSettingInRoutes(): void
    {
        $this->builder->group(new RouteGroupOptions('', null, true), function (RouteCollectionBuilder $registry) {
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $registry->route('GET', '', null, false)
                ->mapsToMethod($controller::class, 'bar');
        });
        $routes = $this->builder->build()->values;
        $this->assertCount(1, $routes);
        $this->assertTrue($routes[0]->uriTemplate->isHttpsOnly);
    }

    public function testNestedGroupOptionsAreAddedCorrectlyToRoute(): void
    {
        $middleware1 = new class () {
        };
        $middleware2 = new class () {
        };
        $middleware3 = new class () {
        };
        $outerConstraints = [$this->createMock(IRouteConstraint::class)];
        $outerGroupMiddlewareBinding = new MiddlewareBinding($middleware1::class);
        $innerConstraints = [$this->createMock(IRouteConstraint::class)];
        $innerGroupMiddlewareBinding = new MiddlewareBinding($middleware2::class);
        $routeMiddlewareBinding = new MiddlewareBinding($middleware3::class);
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
                        $controller = new class () {
                            public function bar(): void
                            {
                            }
                        };
                        // Use the bulk-with method so we can pass in an already-instantiated object to check against later
                        $registry->route('GET', 'rp')
                            ->mapsToMethod($controller::class, 'bar')
                            ->withManyMiddleware([$routeMiddlewareBinding]);
                    }
                );
            }
        );
        $routes = $this->builder->build()->values;
        $this->assertCount(1, $routes);
        $this->assertSame('/op/ip/rp', $routes[0]->uriTemplate->pathTemplate);
        $this->assertContains($outerConstraints[0], $routes[0]->constraints);
        $this->assertContains($innerConstraints[0], $routes[0]->constraints);
        $expectedMiddlewareBindings = [
            $outerGroupMiddlewareBinding,
            $innerGroupMiddlewareBinding,
            $routeMiddlewareBinding
        ];
        $this->assertEquals($expectedMiddlewareBindings, $routes[0]->middlewareBindings);
    }

    public function testRouteAddsLeadingSlashToPath(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $this->builder->route('GET', 'foo')
            ->mapsToMethod($controller::class, 'bar');
        $routes = $this->builder->build()->values;
        $this->assertCount(1, $routes);
        $this->assertSame('/foo', $routes[0]->uriTemplate->pathTemplate);
    }

    public function testRouteBuilderIsCreatedWithConstraints(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $constraints = [$this->createMock(IRouteConstraint::class)];
        $routeBuilder = $this->builder->route('GET', '')
            ->mapsToMethod($controller::class, 'bar')
            ->withManyConstraints($constraints);
        $route = $routeBuilder->build();
        $this->assertContains($constraints[0], $route->constraints);
    }

    public function testRouteBuilderIsCreatedWithHttpMethodParameterSet(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $routeBuilder = $this->builder->route(['GET', 'DELETE'], '')
            ->mapsToMethod($controller::class, 'bar');
        $route = $routeBuilder->build();
        $this->assertCount(1, $route->constraints);
        $httpMethodRouteConstraint = $route->constraints[0];
        $this->assertInstanceOf(HttpMethodRouteConstraint::class, $httpMethodRouteConstraint);
        // HEAD is automatically inserted for GET routes
        $this->assertEquals(['GET', 'DELETE', 'HEAD'], $httpMethodRouteConstraint->allowedMethods);
    }

    public function testRouteBuilderIsCreatedWithParametersToMatchParameter(): void
    {
        $controller = new class () {
            public function bar(): void
            {
            }
        };
        $routeBuilder = $this->builder->route('GET', '')
            ->mapsToMethod($controller::class, 'bar')
            ->withParameter('FOO', 'BAR');
        $route = $routeBuilder->build();
        $this->assertEquals(['FOO' => 'BAR'], $route->parameters);
    }

    public function testRouteConvenienceMethodsCreateRoutesWithProperMethods(): void
    {
        foreach (['DELETE', 'GET', 'OPTIONS', 'PATCH', 'POST', 'PUT'] as $httpMethod) {
            /** @var RouteBuilder $routeBuilder */
            $routeBuilder = $this->builder->{\strtolower($httpMethod)}('foo');
            $controller = new class () {
                public function bar(): void
                {
                }
            };
            $routeBuilder->mapsToMethod($controller::class, 'bar');
            $route = $routeBuilder->build();
            $this->assertCount(1, $route->constraints);
            $methodConstraint = $route->constraints[0];
            $this->assertInstanceOf(HttpMethodRouteConstraint::class, $methodConstraint);
            /**
             * Specifically checking contains as opposed to equals because some constraints, eg GET, might contain
             * additional methods, eg HEAD
             */
            $this->assertContains($httpMethod, $methodConstraint->allowedMethods);
        }
    }
}
