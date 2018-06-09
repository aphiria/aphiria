<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/route-matcher/blob/master/LICENSE.md
 */

namespace Opulence\Routing\Tests\Builders;

use Opulence\Routing\Builders\RouteBuilderRegistry;
use Opulence\Routing\Builders\RouteGroupOptions;
use Opulence\Routing\Middleware\MiddlewareBinding;
use Opulence\Routing\UriTemplates\Compilers\IUriTemplateCompiler;
use Opulence\Routing\UriTemplates\UriTemplate;

/**
 * Tests the route builder registry
 */
class RouteBuilderRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var RouteBuilderRegistry The registry to use in tests */
    private $registry;
    /** @var IUriTemplateCompiler|\PHPUnit_Framework_MockObject_MockObject The URI template compiler to use within the registry */
    private $uriTemplateCompiler;

    public function setUp(): void
    {
        $this->uriTemplateCompiler = $this->createMock(IUriTemplateCompiler::class);
        $this->registry = new RouteBuilderRegistry($this->uriTemplateCompiler);
    }

    public function testBuildingWithNoRoutesReturnsEmptyCollection(): void
    {
        $routes = $this->registry->buildAll();
        $httpMethods = [
            'DELETE',
            'GET',
            'POST',
            'PUT',
            'HEAD',
            'OPTIONS',
            'PATCH'
        ];

        foreach ($httpMethods as $httpMethod) {
            $this->assertEmpty($routes->getByMethod($httpMethod));
        }
    }

    public function testGroupAttributesToMatchOnAreMergedWithRouteAttributesToMatch(): void
    {
        $groupOptions = new RouteGroupOptions('foo', 'bar', false, [], ['H1' => 'val1']);
        $this->registry->group($groupOptions, function (RouteBuilderRegistry $registry) {
            $registry->map('GET', '', '', false)
                ->toMethod('foo', 'bar')
                ->withAttribute('H2', 'val2');
        });
        $routes = $this->registry->buildAll()->getByMethod('GET');
        $this->assertCount(1, $routes);
        $this->assertEquals(['H1' => 'val1', 'H2' => 'val2'], $routes[0]->getAttributes());
    }

    public function testGroupOptionsDoNotApplyToRoutesAddedOutsideGroup(): void
    {
        // The route we're testing is added second, which is why we're testing at(1)
        $this->uriTemplateCompiler->expects($this->at(1))
            ->method('compile')
            ->with('rh2', 'rp2', true)
            ->willReturn($this->createMock(UriTemplate::class));
        $groupOptions = new RouteGroupOptions('gp', 'gh', false);
        $this->registry->group($groupOptions, function (RouteBuilderRegistry $registry) {
            $registry->map('GET', 'rp1')
                ->toMethod('c1', 'm1');
        });
        $this->registry->map('POST', 'rp2', 'rh2', true)
            ->toMethod('c2', 'm2');
        $routes = $this->registry->buildAll()->getByMethod('POST');
        $this->assertCount(1, $routes);
    }

    public function testGroupingAppendsToRouteHostTemplate(): void
    {
        $this->uriTemplateCompiler->expects($this->once())
            ->method('compile')
            ->with('barbaz', 'foo', false)
            ->willReturn($this->createMock(UriTemplate::class));
        $groupOptions = new RouteGroupOptions('foo', 'baz', false);
        $this->registry->group($groupOptions, function (RouteBuilderRegistry $registry) {
            $registry->map('GET', '', 'bar')
                ->toMethod('controller', 'method');
        });
    }

    public function testGroupMiddlewareAreMergedWithRouteMiddleware(): void
    {
        $groupMiddlewareBinding = new MiddlewareBinding('foo');
        $routeMiddlewareBinding = new MiddlewareBinding('bar');
        $groupOptions = new RouteGroupOptions('', '', false, [$groupMiddlewareBinding], []);
        $this->registry->group($groupOptions, function (RouteBuilderRegistry $registry) use ($routeMiddlewareBinding) {
            // Use the bulk-with method so we can pass in an already-instantiated object to check against later
            $registry->map('GET', '', null, false)
                ->toMethod('foo', 'bar')
                ->withManyMiddleware([$routeMiddlewareBinding]);
        });
        $routes = $this->registry->buildAll()->getByMethod('GET');
        $this->assertCount(1, $routes);
        $this->assertEquals([$groupMiddlewareBinding, $routeMiddlewareBinding], $routes[0]->getMiddlewareBindings());
    }

    public function testHttpsOnlyGroupOverridesHttpsSettingInRoutes(): void
    {
        $this->uriTemplateCompiler->expects($this->once())
            ->method('compile')
            ->with('', '', true)
            ->willReturn($this->createMock(UriTemplate::class));
        $this->registry->group(new RouteGroupOptions('', '', true), function (RouteBuilderRegistry $registry) {
            $registry->map('GET', '', null, false)
                ->toMethod('foo', 'bar');
        });
    }

    public function testNestedGroupOptionsAreAddedCorrectlyToRoute(): void
    {
        $this->uriTemplateCompiler->expects($this->once())
            ->method('compile')
            ->with('rhihoh', 'opiprp', true)
            ->willReturn($this->createMock(UriTemplate::class));
        $outerGroupMiddlewareBinding = new MiddlewareBinding('foo');
        $innerGroupMiddlewareBinding = new MiddlewareBinding('bar');
        $routeMiddlewareBinding = new MiddlewareBinding('baz');
        $outerGroupOptions = new RouteGroupOptions('op', 'oh', false, [$outerGroupMiddlewareBinding]);
        $this->registry->group(
            $outerGroupOptions,
            function (RouteBuilderRegistry $registry) use ($innerGroupMiddlewareBinding, $routeMiddlewareBinding) {
                $innerGroupOptions = new RouteGroupOptions('ip', 'ih', true, [$innerGroupMiddlewareBinding]);
                $registry->group(
                    $innerGroupOptions,
                    function (RouteBuilderRegistry $registry) use ($routeMiddlewareBinding) {
                        // Use the bulk-with method so we can pass in an already-instantiated object to check against later
                        $registry->map('GET', 'rp', 'rh', false)
                            ->toMethod('foo', 'bar')
                            ->withManyMiddleware([$routeMiddlewareBinding]);
                    }
                );
            }
        );
        $routes = $this->registry->buildAll()->getByMethod('GET');
        $this->assertCount(1, $routes);
        $expectedMiddlewareBindings = [
            $outerGroupMiddlewareBinding,
            $innerGroupMiddlewareBinding,
            $routeMiddlewareBinding
        ];
        $this->assertEquals($expectedMiddlewareBindings, $routes[0]->getMiddlewareBindings());
    }

    public function testGroupingPrependsToRoutePathTemplate(): void
    {
        $this->uriTemplateCompiler->expects($this->once())
            ->method('compile')
            ->with(null, 'foobar', false)
            ->willReturn($this->createMock(UriTemplate::class));
        $groupOptions = new RouteGroupOptions('foo', '', false);
        $this->registry->group($groupOptions, function (RouteBuilderRegistry $registry) {
            $registry->map('GET', 'bar')
                ->toMethod('controller', 'method');
        });
    }

    public function testRouteBuilderIsCreatedWithAttributesToMatchParameter(): void
    {
        $routeBuilder = $this->registry->map('GET', '', null, false)
            ->toMethod('foo', 'bar')
            ->withAttribute('FOO', 'BAR');
        $route = $routeBuilder->build();
        $this->assertEquals(['FOO' => 'BAR'], $route->getAttributes());
    }

    public function testRouteBuilderIsCreatedWithHttpMethodParameterSet(): void
    {
        $routeBuilder = $this->registry->map(['GET', 'DELETE'], '')
            ->toMethod('foo', 'bar');
        $route = $routeBuilder->build();
        $this->assertEquals(['GET', 'DELETE'], $route->getHttpMethods());
    }
}
