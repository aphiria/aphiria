<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Router;
use Aphiria\Api\Tests\Controllers\Mocks\ControllerWithEndpoints as ControllerMock;
use Aphiria\Api\Tests\Mocks\MiddlewareThatIncrementsHeader;
use Aphiria\Api\Tests\Mocks\ParameterizedMiddleware;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Uri;
use Aphiria\Routing\Matchers\IRouteMatcher;
use Aphiria\Routing\Matchers\RouteMatchingResult;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\UriTemplate;
use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    private Router $router;
    private IRouteMatcher&MockObject $routeMatcher;
    private IServiceResolver&MockObject $serviceResolver;
    private IContentNegotiator&MockObject $contentNegotiator;
    private IRouteActionInvoker&MockObject $routeActionInvoker;

    protected function setUp(): void
    {
        $this->routeMatcher = $this->createMock(IRouteMatcher::class);
        $this->serviceResolver = $this->createMock(IServiceResolver::class);
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->routeActionInvoker = $this->createMock(IRouteActionInvoker::class);
        $this->router = new Router(
            $this->routeMatcher,
            $this->serviceResolver,
            $this->contentNegotiator,
            $this->routeActionInvoker
        );
    }

    public function testAttributeMiddlewareIsResolvedAndAttributesAreSet(): void
    {
        $middleware = new ParameterizedMiddleware();
        $middlewareBinding = new MiddlewareBinding(ParameterizedMiddleware::class, ['foo' => 'bar']);
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $controller = new ControllerMock();
        $this->serviceResolver->method('resolve')
            ->willReturnMap([[ControllerMock::class, $controller], [ParameterizedMiddleware::class, $middleware]]);
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(ControllerMock::class, 'noParameters'),
                [],
                [$middlewareBinding]
            ),
            [],
            []
        );
        $this->routeMatcher->expects($this->once())
            ->method('matchRoute')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchingResult);
        $this->router->handle($request);
        // Test that the middleware actually set the headers
        $this->assertSame('bar', $middleware->getParameter('foo'));
    }

    public function testInvalidMiddlewareThrowsExceptionThatIsCaught(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Middleware %s does not implement %s', RouterTest::class, IMiddleware::class));
        $middleware = $this;
        $middlewareBinding = new MiddlewareBinding(__CLASS__);
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $controller = new ControllerMock();
        $this->serviceResolver->method('resolve')
            ->willReturnMap([[ControllerMock::class, $controller], [__CLASS__, $middleware]]);
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(ControllerMock::class, 'noParameters'),
                [],
                [$middlewareBinding]
            ),
            [],
            []
        );
        $this->routeMatcher->expects($this->once())
            ->method('matchRoute')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchingResult);
        $this->router->handle($request);
    }

    public function testMethodNotAllowedSetsAllowHeaderInExceptionResponse(): void
    {
        $exceptionThrown = false;

        try {
            $request = $this->createRequestMock('GET', 'http://foo.com/bar');
            $this->routeMatcher->expects($this->once())
                ->method('matchRoute')
                ->with('GET', 'foo.com', '/bar')
                ->willReturn(new RouteMatchingResult(null, [], ['GET']));
            $this->router->handle($request);
        } catch (HttpException $ex) {
            $exceptionThrown = true;
            $this->assertSame('GET', $ex->response->getHeaders()->getFirst('Allow'));
        }

        $this->assertTrue($exceptionThrown, 'Failed to throw exception');
    }

    public function testMiddlewareIsResolvedAndIsInvokedInCorrectOrder(): void
    {
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $expectedHeaders = new Headers();
        $expectedResponse = $this->createMock(IResponse::class);
        $expectedResponse->method('getHeaders')
            ->willReturn($expectedHeaders);
        // We want different middleware class names to be able to test multiple middleware, hence the anon classes
        $middleware1 = new class () extends MiddlewareThatIncrementsHeader {
        };
        $middleware2 = new class () extends MiddlewareThatIncrementsHeader {
        };
        $controller = new ControllerMock();
        $this->serviceResolver->method('resolve')
            ->willReturnMap([
                [ControllerMock::class, $controller],
                [$middleware1::class, $middleware1],
                [$middleware2::class, $middleware2]
            ]);
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(ControllerMock::class, 'noParameters'),
                [],
                [
                    new MiddlewareBinding($middleware1::class),
                    new MiddlewareBinding($middleware2::class)
                ]
            ),
            [],
            []
        );
        $this->routeMatcher->expects($this->once())
            ->method('matchRoute')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchingResult);
        $this->routeActionInvoker->expects($this->once())
            ->method('invokeRouteAction')
            ->with(Closure::fromCallable([$controller, 'noParameters']))
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->router->handle($request));
        // Test that the middleware actually set the headers
        $this->assertEquals([1, 2], $expectedHeaders->get('Foo'));
    }

    public function testNoMatchingRouteThrows404Exception(): void
    {
        try {
            $request = $this->createRequestMock('GET', 'http://foo.com/bar');
            $this->routeMatcher->expects($this->once())
                ->method('matchRoute')
                ->with('GET', 'foo.com', '/bar')
                ->willReturn(new RouteMatchingResult(null, [], []));
            $this->router->handle($request);
            $this->fail('Failed to throw exception');
        } catch (HttpException $ex) {
            $this->assertSame(HttpStatusCode::NotFound, $ex->response->getStatusCode());
        }
    }

    public function testRouteActionWithControllerClassNameResolvesItAndInvokesIt(): void
    {
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $expectedResponse = $this->createMock(IResponse::class);
        $controller = new ControllerMock();
        $this->serviceResolver->expects($this->once())
            ->method('resolve')
            ->with(ControllerMock::class)
            ->willReturn($controller);
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(ControllerMock::class, 'noParameters'),
                [],
                []
            ),
            [],
            []
        );
        $this->routeMatcher->expects($this->once())
            ->method('matchRoute')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchingResult);
        $this->routeActionInvoker->expects($this->once())
            ->method('invokeRouteAction')
            ->with(Closure::fromCallable([$controller, 'noParameters']))
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->router->handle($request));
        // Verify the request was set
        $this->assertSame($request, $controller->getRequest());
    }

    public function testRouteActionWithInvalidControllerInstanceThrowsExceptionThatIsCaught(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('Controller %s does not extend %s', RouterTest::class, Controller::class));
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        // Purposely bind a non-controller class's method to the route action
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(__CLASS__, __METHOD__),
                [],
                []
            ),
            [],
            []
        );
        $this->serviceResolver->expects($this->once())
            ->method('resolve')
            ->with(__CLASS__)
            ->willReturn($this);
        $this->routeMatcher->expects($this->once())
            ->method('matchRoute')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchingResult);
        $this->router->handle($request);
    }

    /**
     * Creates a mock request with a few properties set
     *
     * @param string $method The HTTP method to use
     * @param string $uri The URI to use
     * @return IRequest&MockObject The mocked request
     */
    private function createRequestMock(string $method, string $uri): IRequest&MockObject
    {
        $request = $this->createMock(IRequest::class);
        $request->method('getMethod')
            ->willReturn($method);
        $request->method('getUri')
            ->willReturn(new Uri($uri));

        return $request;
    }
}
