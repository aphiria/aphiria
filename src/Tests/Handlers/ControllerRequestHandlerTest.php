<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Handlers;

use Closure;
use InvalidArgumentException;
use Opulence\Api\Controller;
use Opulence\Api\Exceptions\IExceptionHandler;
use Opulence\Api\Handlers\ControllerRequestHandler;
use Opulence\Api\Handlers\IDependencyResolver;
use Opulence\Api\Handlers\IRouteActionInvoker;
use Opulence\Api\Tests\Handlers\Mocks\Controller as ControllerMock;
use Opulence\Api\Tests\Handlers\Mocks\MiddlewareThatAddsHeader;
use Opulence\Api\Tests\Middleware\Mocks\AttributeMiddleware;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Uri;
use Opulence\Routing\Matchers\IRouteMatcher;
use Opulence\Routing\Matchers\MatchedRoute;
use Opulence\Routing\Matchers\RouteNotFoundException;
use Opulence\Routing\Middleware\MiddlewareBinding;
use Opulence\Routing\RouteAction;

/**
 * Tests the controller request handler
 */
class ControllerRequestHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ControllerRequestHandler The handler to use in tests */
    private $requestHandler;
    /** @var IExceptionHandler|\PHPUnit_Framework_MockObject_MockObject The exception handler to use */
    private $exceptionHandler;
    /** @var IDependencyResolver|\PHPUnit_Framework_MockObject_MockObject The dependency resolver to use */
    private $dependencyResolver;
    /** @var IRouteActionInvoker|\PHPUnit_Framework_MockObject_MockObject The route action invoker to use */
    private $routeActionInvoker;
    /** @var IContentNegotiator|\PHPUnit_Framework_MockObject_MockObject The content negotiator to use */
    private $contentNegotiator;
    /** @var IRouteMatcher|\PHPUnit_Framework_MockObject_MockObject The route matcher to use */
    private $routeMatcher;

    public function setUp(): void
    {
        $this->routeMatcher = $this->createMock(IRouteMatcher::class);
        $this->dependencyResolver = $this->createMock(IDependencyResolver::class);
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->routeActionInvoker = $this->createMock(IRouteActionInvoker::class);
        $this->exceptionHandler = $this->createMock(IExceptionHandler::class);
        $this->requestHandler = new ControllerRequestHandler(
            $this->routeMatcher,
            $this->dependencyResolver,
            $this->contentNegotiator,
            $this->routeActionInvoker,
            $this->exceptionHandler
        );
    }

    public function testAttributeMiddlewareIsResolvedAndAttributesAreSet(): void
    {
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $middleware = new AttributeMiddleware();
        $controller = new ControllerMock();
        $this->dependencyResolver->expects($this->at(0))
            ->method('resolve')
            ->with(ControllerMock::class)
            ->willReturn($controller);
        $this->dependencyResolver->expects($this->at(1))
            ->method('resolve')
            ->with(AttributeMiddleware::class)
            ->willReturn($middleware);
        $matchedRoute = new MatchedRoute(
            new RouteAction(ControllerMock::class, 'noParameters', null),
            [],
            [new MiddlewareBinding(AttributeMiddleware::class, ['foo' => 'bar'])]
        );
        $this->routeMatcher->expects($this->once())
            ->method('match')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchedRoute);
        $this->routeActionInvoker->expects($this->once())
            ->method('invokeRouteAction')
            ->with([$controller, 'noParameters'])
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->requestHandler->handle($request));
        // Test that the middleware actually set the headers
        $this->assertEquals('bar', $middleware->getAttribute('foo'));
    }

    public function testInvalidMiddlewareThrowsExceptionThatIsCaught(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $middleware = $this;
        $controller = new ControllerMock();
        $this->dependencyResolver->expects($this->at(0))
            ->method('resolve')
            ->with(ControllerMock::class)
            ->willReturn($controller);
        $this->dependencyResolver->expects($this->at(1))
            ->method('resolve')
            ->with(__CLASS__)
            ->willReturn($middleware);
        $matchedRoute = new MatchedRoute(
            new RouteAction(ControllerMock::class, 'noParameters', null),
            [],
            [new MiddlewareBinding(__CLASS__)]
        );
        $this->routeMatcher->expects($this->once())
            ->method('match')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchedRoute);
        $this->requestHandler->handle($request);
    }

    public function testMiddlewareIsResolvedAndIsInvoked(): void
    {
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $expectedHeaders = new HttpHeaders();
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $expectedResponse->expects($this->once())
            ->method('getHeaders')
            ->willReturn($expectedHeaders);
        $middleware = new MiddlewareThatAddsHeader();
        $controller = new ControllerMock();
        $this->dependencyResolver->expects($this->at(0))
            ->method('resolve')
            ->with(ControllerMock::class)
            ->willReturn($controller);
        $this->dependencyResolver->expects($this->at(1))
            ->method('resolve')
            ->with(MiddlewareThatAddsHeader::class)
            ->willReturn($middleware);
        $matchedRoute = new MatchedRoute(
            new RouteAction(ControllerMock::class, 'noParameters', null),
            [],
            [new MiddlewareBinding(MiddlewareThatAddsHeader::class)]
        );
        $this->routeMatcher->expects($this->once())
            ->method('match')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchedRoute);
        $this->routeActionInvoker->expects($this->once())
            ->method('invokeRouteAction')
            ->with([$controller, 'noParameters'])
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->requestHandler->handle($request));
        // Test that the middleware actually set the headers
        $this->assertEquals('bar', $expectedHeaders->getFirst('Foo'));
    }

    public function testNoMatchingRouteThrows404ThatIsCaught(): void
    {
        $this->expectException(HttpException::class);
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $this->routeMatcher->expects($this->once())
            ->method('match')
            ->with('GET', 'foo.com', '/bar')
            ->willThrowException(new RouteNotFoundException());
        $this->requestHandler->handle($request);
    }

    public function testRouteActionWithClosureControllerBindsItToControllerObjectAndInvokesIt(): void
    {
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $controllerClosure = function () {
            // Purposely getting $this to verify that it's bound to an instance of Controller later on
            return $this;
        };
        $matchedRoute = new MatchedRoute(new RouteAction(null, null, $controllerClosure), [], []);
        $this->routeMatcher->expects($this->once())
            ->method('match')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchedRoute);
        $this->routeActionInvoker->expects($this->once())
            ->method('invokeRouteAction')
            ->with($this->callback(function (Closure $closure) {
                // Theoretically, this should return the $this, but now bound to a controller instance
                /** @var Controller $boundController */
                $boundController = $closure();

                return $boundController instanceof Controller;
            }))
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->requestHandler->handle($request));
    }

    public function testRouteActionWithControllerClassNameResolvesItAndInvokesIt(): void
    {
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $controller = new ControllerMock();
        $this->dependencyResolver->expects($this->once())
            ->method('resolve')
            ->with(ControllerMock::class)
            ->willReturn($controller);
        $matchedRoute = new MatchedRoute(new RouteAction(ControllerMock::class, 'noParameters', null), [], []);
        $this->routeMatcher->expects($this->once())
            ->method('match')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchedRoute);
        $this->routeActionInvoker->expects($this->once())
            ->method('invokeRouteAction')
            ->with([$controller, 'noParameters'])
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->requestHandler->handle($request));
        // Verify the request context was set
        $this->assertSame($request, $controller->getRequestContext()->getRequest());
        $this->assertSame($matchedRoute, $controller->getRequestContext()->getMatchedRoute());
    }

    public function testRouteActionWithInvalidControllerInstanceThrowsExceptionThatIsCaught(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        // Purposely bind a non-controller class's method to the route action
        $matchedRoute = new MatchedRoute(new RouteAction(__CLASS__, __METHOD__, null), [], []);
        $this->dependencyResolver->expects($this->once())
            ->method('resolve')
            ->with(__CLASS__)
            ->willReturn($this);
        $this->routeMatcher->expects($this->once())
            ->method('match')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchedRoute);
        $this->requestHandler->handle($request);
    }

    /**
     * Creates a mock request with a few properties set
     *
     * @param string $method The HTTP method to use
     * @param string $uri The URI to use
     * @return IHttpRequestMessage|\PHPUnit_Framework_MockObject_MockObject The mocked request
     */
    private function createRequestMock(string $method, string $uri): IHttpRequestMessage
    {
        $request = $this->createMock(IHttpRequestMessage::class);
        $request->expects($this->any())
            ->method('getMethod')
            ->willReturn($method);
        $request->expects($this->any())
            ->method('getUri')
            ->willReturn(new Uri($uri));

        return $request;
    }
}
