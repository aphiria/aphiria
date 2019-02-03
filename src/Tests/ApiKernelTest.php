<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

namespace Aphiria\Api\Tests;

use Closure;
use InvalidArgumentException;
use Aphiria\Api\ApiKernel;
use Aphiria\Api\Controllers\Controller;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\IDependencyResolver;
use Aphiria\Api\Tests\Controllers\Mocks\Controller as ControllerMock;
use Aphiria\Api\Tests\Mocks\MiddlewareThatIncrementsHeader;
use Aphiria\Api\Tests\Mocks\AttributeMiddleware;
use Aphiria\Middleware\MiddlewarePipelineFactory;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Uri;
use Aphiria\Routing\Matchers\IRouteMatcher;
use Aphiria\Routing\Matchers\RouteMatchingResult;
use Aphiria\Routing\Middleware\MiddlewareBinding;
use Aphiria\Routing\Route;
use Aphiria\Routing\RouteAction;
use Aphiria\Routing\UriTemplates\UriTemplate;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the API kernel
 */
class ApiKernelTest extends TestCase
{
    /** @var ApiKernel */
    private $apiKernel;
    /** @var IRouteMatcher|MockObject */
    private $routeMatcher;
    /** @var IDependencyResolver|MockObject */
    private $dependencyResolver;
    /** @var IContentNegotiator|MockObject */
    private $contentNegotiator;
    /** @var MiddlewarePipelineFactory */
    private $middlewarePipelineFactory;
    /** @var IRouteActionInvoker|MockObject */
    private $routeActionInvoker;

    public function setUp(): void
    {
        $this->routeMatcher = $this->createMock(IRouteMatcher::class);
        $this->dependencyResolver = $this->createMock(IDependencyResolver::class);
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->middlewarePipelineFactory = new MiddlewarePipelineFactory();
        $this->routeActionInvoker = $this->createMock(IRouteActionInvoker::class);
        $this->apiKernel = new ApiKernel(
            $this->routeMatcher,
            $this->dependencyResolver,
            $this->contentNegotiator,
            $this->middlewarePipelineFactory,
            $this->routeActionInvoker
        );
    }

    public function testAttributeMiddlewareIsResolvedAndAttributesAreSet(): void
    {
        $middleware = new AttributeMiddleware();
        $middlewareBinding = new MiddlewareBinding(AttributeMiddleware::class, ['foo' => 'bar']);
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $controller = new ControllerMock();
        $this->dependencyResolver->expects($this->at(0))
            ->method('resolve')
            ->with(ControllerMock::class)
            ->willReturn($controller);
        $this->dependencyResolver->expects($this->at(1))
            ->method('resolve')
            ->with(AttributeMiddleware::class)
            ->willReturn($middleware);
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(ControllerMock::class, 'noParameters', null),
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
        $this->apiKernel->handle($request);
        // Test that the middleware actually set the headers
        $this->assertEquals('bar', $middleware->getAttribute('foo'));
    }

    public function testInvalidMiddlewareThrowsExceptionThatIsCaught(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $middleware = $this;
        $middlewareBinding = new MiddlewareBinding(__CLASS__);
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $controller = new ControllerMock();
        $this->dependencyResolver->expects($this->at(0))
            ->method('resolve')
            ->with(ControllerMock::class)
            ->willReturn($controller);
        $this->dependencyResolver->expects($this->at(1))
            ->method('resolve')
            ->with(__CLASS__)
            ->willReturn($middleware);
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(ControllerMock::class, 'noParameters', null),
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
        $this->apiKernel->handle($request);
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
            $this->apiKernel->handle($request);
        } catch (HttpException $ex) {
            $exceptionThrown = true;
            $this->assertEquals('GET', $ex->getResponse()->getHeaders()->getFirst('Allow'));
        }

        $this->assertTrue($exceptionThrown, 'Failed to throw exception');
    }

    public function testMiddlewareIsResolvedAndIsInvokedInCorrectOrder(): void
    {
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $expectedHeaders = new HttpHeaders();
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $expectedResponse->method('getHeaders')
            ->willReturn($expectedHeaders);
        $middleware1 = new MiddlewareThatIncrementsHeader();
        $middleware2 = new MiddlewareThatIncrementsHeader();
        $controller = new ControllerMock();
        $this->dependencyResolver->expects($this->at(0))
            ->method('resolve')
            ->with(ControllerMock::class)
            ->willReturn($controller);
        $this->dependencyResolver->expects($this->at(1))
            ->method('resolve')
            ->with(MiddlewareThatIncrementsHeader::class)
            ->willReturn($middleware1);
        $this->dependencyResolver->expects($this->at(2))
            ->method('resolve')
            ->with(MiddlewareThatIncrementsHeader::class)
            ->willReturn($middleware2);
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(ControllerMock::class, 'noParameters', null),
                [],
                [
                    new MiddlewareBinding(MiddlewareThatIncrementsHeader::class),
                    new MiddlewareBinding(MiddlewareThatIncrementsHeader::class)
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
            ->with([$controller, 'noParameters'])
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->apiKernel->handle($request));
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
            $this->apiKernel->handle($request);
            $this->fail('Failed to throw exception');
        } catch (HttpException $ex) {
            $this->assertEquals(HttpStatusCodes::HTTP_NOT_FOUND, $ex->getResponse()->getStatusCode());
        }
    }

    public function testRouteActionWithClosureControllerBindsItToControllerObjectAndInvokesIt(): void
    {
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $controllerClosure = function () {
            // Purposely getting $this to verify that it's bound to an instance of Controller later on
            return $this;
        };
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(null, null, $controllerClosure),
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
            ->with($this->callback(function (Closure $closure) {
                // Theoretically, this should return the $this, but now bound to a controller instance
                /** @var Controller $boundController */
                $boundController = $closure();

                return $boundController instanceof Controller;
            }))
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->apiKernel->handle($request));
    }

    public function testRouteActionWithNonExistentControllerMethodThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        $controller = new ControllerMock();
        $this->dependencyResolver->expects($this->once())
            ->method('resolve')
            ->with(ControllerMock::class)
            ->willReturn($controller);
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(ControllerMock::class, 'doesNotExist', null),
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
        $this->apiKernel->handle($request);
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
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(ControllerMock::class, 'noParameters', null),
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
            ->with([$controller, 'noParameters'])
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->apiKernel->handle($request));
        // Verify the request was set
        $this->assertSame($request, $controller->getRequest());
    }

    public function testRouteActionWithInvalidControllerInstanceThrowsExceptionThatIsCaught(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $request = $this->createRequestMock('GET', 'http://foo.com/bar');
        // Purposely bind a non-controller class's method to the route action
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(__CLASS__, __METHOD__, null),
                [],
                []
            ),
            [],
            []
        );
        $this->dependencyResolver->expects($this->once())
            ->method('resolve')
            ->with(__CLASS__)
            ->willReturn($this);
        $this->routeMatcher->expects($this->once())
            ->method('matchRoute')
            ->with('GET', 'foo.com', '/bar')
            ->willReturn($matchingResult);
        $this->apiKernel->handle($request);
    }

    /**
     * Creates a mock request with a few properties set
     *
     * @param string $method The HTTP method to use
     * @param string $uri The URI to use
     * @return IHttpRequestMessage|MockObject The mocked request
     */
    private function createRequestMock(string $method, string $uri): IHttpRequestMessage
    {
        $request = $this->createMock(IHttpRequestMessage::class);
        $request->method('getMethod')
            ->willReturn($method);
        $request->method('getUri')
            ->willReturn(new Uri($uri));

        return $request;
    }
}
