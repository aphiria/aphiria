<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Routing;

use Aphiria\Framework\Routing\RouteRequestCreationException;
use Aphiria\Framework\Routing\RouteRequestFactory;
use Aphiria\Routing\Attributes\Header;
use Aphiria\Routing\Attributes\QueryString;
use Aphiria\Routing\Attributes\RouteVariable;
use Aphiria\Routing\Matchers\Constraints\HttpMethodRouteConstraint;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\RouteCollection;
use Aphiria\Routing\UriTemplates\IRouteUriFactory;
use Aphiria\Routing\UriTemplates\RouteUriCreationException;
use Aphiria\Routing\UriTemplates\UriTemplate;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class RouteRequestFactoryTest extends TestCase
{
    private RouteCollection $routes;
    private RouteRequestFactory $factory;

    protected function setUp(): void
    {
        $this->routes = new RouteCollection();
        $this->factory = new RouteRequestFactory($this->routes);
    }

    public function testCreatingGetRequestUsesGetForRequestMethodDespiteAlsoSupportingHead(): void
    {
        $controller = new class () {
            public function foo(): void {}
        };
        $this->addRouteWithUriTemplate('foo', 'GET', null, '/foo', $controller, 'foo');
        $request = $this->factory->createRouteUri('foo');
        $this->assertSame('GET', $request->method);
    }

    public function testCreatingRequestForNonExistentRouteActionThrowsException(): void
    {
        $this->expectException(RouteRequestCreationException::class);
        $controller = new class () {
            public function foo(): void {}
        };
        $this->expectExceptionMessage('Failed to reflect ' . $controller::class . '::bar');
        $this->addRouteWithUriTemplate('foo', 'GET', null, '/foo', $controller, 'bar');
        $this->factory->createRouteUri('foo');
    }

    public function testCreatingRequestForNonExistentRouteThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Route \"foo\" does not exist");
        $this->factory->createRouteUri('foo');
    }

    public function testCreatingRequestForRouteWithMultipleMethodsAndNotSpecifyingAMethodThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method must be specified if there is more than one supported method - route "foo" supports methods GET, POST, HEAD');
        $controller = new class () {
            public function foo(): void {}
        };
        $this->addRouteWithUriTemplate('foo', ['GET', 'POST'], null, '/foo', $controller, 'foo');
        $this->factory->createRouteUri('foo');
    }

    public function testCreatingRequestForRouteWitSingleNonGetMethodCreatesRequestForThatMethod(): void
    {
        $controller = new class () {
            public function foo(): void {}
        };
        $this->addRouteWithUriTemplate('foo', ['POST'], null, '/foo', $controller, 'foo');
        $request = $this->factory->createRouteUri('foo');
        $this->assertSame('POST', $request->method);
    }

    public function testCreatingRequestWithHeaderParameterThatRequiresValueThrowsException(): void
    {
        $this->expectException(RouteRequestCreationException::class);
        $this->expectExceptionMessage('Failed to create route request because the parameter "foo" is required but not provided');
        $controller = new class () {
            public function foo(#[Header] string $foo): void {}
        };
        $this->addRouteWithUriTemplate('foo', 'GET', null, '/foo', $controller, 'foo');
        $this->factory->createRouteUri('foo');
    }

    public function testCreatingRequestWithDefaultValueHeaderParameterSetsHeaderValueToDefaultValue(): void
    {
        $controller = new class () {
            public function foo(#[Header] string $foo = 'bar'): void {}
        };
        $this->addRouteWithUriTemplate('foo', 'GET', null, '/foo', $controller, 'foo');
        $request = $this->factory->createRouteUri('foo');
        $this->assertSame('bar', $request->headers->getFirst('foo'));
    }

    public function testCreatingRequestWithHeaderValueInRouteVariablesUsesThatValue(): void
    {
        $controller = new class () {
            public function foo(#[Header] string $foo): void {}
        };
        $this->addRouteWithUriTemplate('foo', 'GET', null, '/foo', $controller, 'foo');
        $request = $this->factory->createRouteUri('foo', ['foo' => 'bar']);
        $this->assertSame('bar', $request->headers->getFirst('foo'));
    }

    public function testCreatingRequestWithNamedHeaderValueInRouteVariablesUsesThatValue(): void
    {
        $controller = new class () {
            public function foo(#[Header('bar')] string $foo): void {}
        };
        $this->addRouteWithUriTemplate('foo', 'GET', null, '/foo', $controller, 'foo');
        $request = $this->factory->createRouteUri('foo', ['bar' => 'baz']);
        $this->assertSame('baz', $request->headers->getFirst('bar'));
    }

    public function testCreatingRequestWithFullUriSetsMethodUriAndHeaders(): void
    {
        $controller = new class () {
            public function foo(#[Header] string $foo): void {}
        };
        $this->addRouteWithUriTemplate('foo', 'GET', 'example.com', '/foo', $controller, 'foo');
        $request = $this->factory->createRouteUri('foo', ['foo' => 'bar']);
        $this->assertSame('GET', $request->method);
        $this->assertSame('https://example.com/foo', (string)$request->uri);
        $this->assertSame('bar', $request->headers->getFirst('foo'));
    }

    public function testCreatingRequestWithNonHeaderRouteVariablesPopulatesThemInUri(): void
    {
        $controller = new class () {
            public function foo(#[Header] string $foo, #[RouteVariable] string $bar, #[QueryString] string $baz): void {}
        };
        $this->addRouteWithUriTemplate('foo', 'GET', null, '/:bar', $controller, 'foo');
        $request = $this->factory->createRouteUri('foo', ['foo' => '1', 'bar' => '2', 'baz' => '3']);
        $this->assertSame('GET', $request->method);
        $this->assertSame('/2?baz=3', (string)$request->uri);
        $this->assertSame('1', $request->headers->getFirst('foo'));
    }

    public function testCreatingRequestWithRelativePathSetsMethodUriAndHeaders(): void
    {
        $controller = new class () {
            public function foo(#[Header] string $foo): void {}
        };
        $this->addRouteWithUriTemplate('foo', 'GET', null, '/foo', $controller, 'foo');
        $request = $this->factory->createRouteUri('foo', ['foo' => 'bar']);
        $this->assertSame('GET', $request->method);
        $this->assertSame('/foo', (string)$request->uri);
        $this->assertSame('bar', $request->headers->getFirst('foo'));
    }

    public function testCreatingRequestWhoseUriCouldNotBeCreatedThrowsException(): void
    {
        $this->expectException(RouteRequestCreationException::class);
        $this->expectExceptionMessage('Failed to create route request');
        $controller = new class () {
            public function foo(): void {}
        };
        $this->addRouteWithUriTemplate('foo', 'GET', null, '/foo', $controller, 'foo');
        $uriFactory = $this->createMock(IRouteUriFactory::class);
        $uriFactory
            ->expects($this->once())
            ->method('createRouteUri')
            ->willThrowException(new RouteUriCreationException('foo'));
        $factory = new RouteRequestFactory($this->routes, $uriFactory);
        $factory->createRouteUri('foo');
    }

    /**
     * Adds a route with a URI template
     *
     * @param string $name The name of the route to add
     * @param list<string>|string $methods The method or list of methods the route supports
     * @param string|null $hostTemplate The host template
     * @param string $pathTemplate The path template
     * @param object $controller The optional controller to map the route action to
     * @param string $methodName The optional controller method name to map the route action to
     */
    private function addRouteWithUriTemplate(
        string $name,
        string|array $methods,
        ?string $hostTemplate,
        string $pathTemplate,
        object $controller,
        string $methodName
    ): void {
        if (\is_string($methods)) {
            $methods = [$methods];
        }

        $this->routes->add(new Route(
            new UriTemplate($pathTemplate, $hostTemplate),
            new RouteAction($controller::class, $methodName),
            [new HttpMethodRouteConstraint($methods)],
            [],
            $name
        ));
    }
}
