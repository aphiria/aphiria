<?php
namespace Opulence\Router\Builders;

use Opulence\Router\Builders\RouteGroupOptions;
use Opulence\Router\Middleware\MiddlewareBinding;
use Opulence\Router\UriTemplates\Compilers\IUriTemplateCompiler;
use Opulence\Router\UriTemplates\IUriTemplate;

/**
 * Tests the route builder registry
 */
class RouteBuilderRegistryTest extends \PHPUnit\Framework\TestCase
{
    /** @var RouteBuilderRegistry The registry to use in tests */
    private $registry = null;
    /** @var IUriTemplateCompiler|\PHPUnit_Framework_MockObject_MockObject The URI template compiler to use within the registry */
    private $uriTemplateCompiler = null;
    
    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->uriTemplateCompiler = $this->createMock(IUriTemplateCompiler::class);
        $this->registry = new RouteBuilderRegistry($this->uriTemplateCompiler);
    }
    
    /**
     * Tests building with no routes returns an empty collection
     */
    public function testBuildingWithNoRoutesReturnsEmptyCollection() : void
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
    
    /**
     * Tests that an HTTPS-only group overrides the HTTPS setting in its routes
     */
    public function testHttpsOnlyGroupOverridesHttpsSettingInRoutes() : void
    {
        $this->uriTemplateCompiler->expects($this->once())
            ->method('compile')
            ->with('', '', true)
            ->willReturn($this->createMock(IUriTemplate::class));
        $this->registry->group(new RouteGroupOptions('', '', true), function (RouteBuilderRegistry $registry) {
            $registry->map('GET', '', null, false)
                ->toMethod('foo', 'bar');
        });
    }
    
    /**
     * Tests that headers to match on are merged with those in its routes
     */
    public function testGroupHeadersToMatchOnAreMergedWithRouteHeadersToMatch() : void
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
        $this->assertEquals(1, count($routes));
        $this->assertEquals([$groupMiddlewareBinding, $routeMiddlewareBinding], $routes[0]->getMiddlewareBindings());
    }
    
    /**
     * Tests grouping prepends to the host template
     */
    public function testGroupingPrependsToHostTemplate() : void
    {
        $this->fail('Not implemented');
    }
    
    /**
     * Tests grouping appends to the path template
     */
    public function testGroupingAppendsToPathTemplate() : void
    {
        $this->fail('Not implemented');
    }
    
    /**
     * Tests that middleware in the group are merged with middleware in its routes
     */
    public function testGroupMiddlewareAreMergedWithRouteMiddleware() : void
    {
        $this->fail('Not implemented');
    }
    
    /**
     * Tests that nested group options are added correctly to the route
     */
    public function testNestedGroupOptionsAreAddedCorrectlyToRoute() : void
    {
        $this->fail('Not implemented');
    }
    
    /**
     * Tests that the route builder is created with the headers to match parameter
     */
    public function testRouteBuilderIsCreatedWithHeadersToMatchParameter() : void
    {
        $this->fail('Not implemented');
    }
    
    /**
     * Tests that the route builder is created with the HTTP method parameter
     */
    public function testRouteBuilderIsCreatedWithHttpMethodParameterSet() : void
    {
        $this->fail('Not implemented');
    }
    
    /**
     * Tests that the URI template is compiled using the URI template parameters
     */
    public function testUriTemplateIsCompiledUsingUriTemplateParameters() : void
    {
        $this->fail('Not implemented');
    }
}
