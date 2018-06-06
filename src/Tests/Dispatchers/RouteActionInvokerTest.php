<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Dispatchers;

use Opulence\Api\ControllerContext;
use Opulence\Api\Dispatchers\RouteActionInvoker;
use Opulence\Api\Tests\Dispatchers\Mocks\ApiController;
use Opulence\Api\Tests\Dispatchers\Mocks\User;
use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\ContentNegotiation\IMediaTypeFormatter;
use Opulence\Net\Http\Request;
use Opulence\Net\Http\StringBody;
use Opulence\Net\Uri;
use Opulence\Routing\Matchers\MatchedRoute;
use Opulence\Routing\Matchers\RouteAction;
use Opulence\Serialization\SerializationException;
use RuntimeException;

/**
 * Tests the route action invoker
 */
class RouteActionInvokerTest extends \PHPUnit\Framework\TestCase
{
    /** @var RouteActionInvoker The invoker to use in tests */
    private $invoker;
    /** @var ApiController The controller to use in tests */
    private $controller;

    public function setUp(): void
    {
        $this->invoker = new RouteActionInvoker();
        $this->controller = new ApiController();
    }

    public function testInvokingMethodThatThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                null,
                null,
                new MatchedRoute(new RouteAction(ApiController::class, 'throwsHttpException', null), [], [])
            )
        );
    }

    public function testInvokingMethodWithObjectParameterReadsFromRequestBodyFirst(): void
    {
        $request = $this->createRequest('http://foo.com');
        $request->setBody(new StringBody('dummy body'));
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->expects($this->once())
            ->method('readFromStream')
            ->with($request->getBody()->readAsStream(), User::class)
            ->willReturn(new User(123, 'foo@bar.com'));
        $response = $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(new RouteAction(ApiController::class, 'objectParameter', null), [], [])
            )
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('id:123, email:foo@bar.com', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithObjectParameterReadsFromQueryStringSecond(): void
    {
        $request = $this->createRequest('http://foo.com?id=123&email=foo%40bar.com');
        $request->setBody(new StringBody('dummy body'));
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->expects($this->at(0))
            ->method('readFromStream')
            ->with($request->getBody()->readAsStream(), User::class)
            ->will($this->throwException(new SerializationException));
        $mediaTypeFormatter->expects($this->at(1))
            ->method('readFromStream')
            ->with($this->callback(function (IStream $stream) {
                return (string)$stream === 'id=123&email=foo%40bar.com';
            }), User::class)
            ->willReturn(new User(123, 'foo@bar.com'));
        $response = $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(new RouteAction(ApiController::class, 'objectParameter', null), [], [])
            )
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('id:123, email:foo@bar.com', $response->getBody()->readAsString());
    }

    public function testInvokingNonExistentMethodThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                null,
                null,
                new MatchedRoute(new RouteAction(ApiController::class, 'doesNotExist', null), [], [])
            )
        );
    }

    public function testInvokingPrivateMethodThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                null,
                null,
                new MatchedRoute(new RouteAction(ApiController::class, 'privateMethod', null), [], [])
            )
        );
    }

    public function testInvokingProtectedMethodThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                null,
                null,
                new MatchedRoute(new RouteAction(ApiController::class, 'protectedMethod', null), [], [])
            )
        );
    }

    /**
     * Creates a request with the input URI
     *
     * @param string $uri The URI to use
     * @return Request The request
     */
    private function createRequest(string $uri): Request
    {
        return new Request('GET', new Uri($uri));
    }
}
