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
use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\ContentNegotiation\IMediaTypeFormatter;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\Request;
use Opulence\Net\Http\StringBody;
use Opulence\Net\Uri;
use Opulence\Routing\Matchers\MatchedRoute;
use Opulence\Routing\RouteAction;
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

    public function testInvokingMethodThatThrowsExceptionThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                null,
                null,
                new MatchedRoute(new RouteAction(ApiController::class, 'throwsException', null), [], [])
            )
        );
    }

    public function testInvokingMethodWithNoParametersIsSuccessful(): void
    {
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $response = $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(new RouteAction(ApiController::class, 'noParameters', null), [], [])
            )
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('noParameters', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithNoTypeHintUsesVariableFromRequest(): void
    {
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $response = $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(
                    new RouteAction(ApiController::class, 'noTypeHintParameter', null),
                    ['foo' => 'bar'],
                    []
                )
            )
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('bar', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithNullableObjectParameterWithBodyThatCannotDeserializeToTypePassesNull(): void
    {
        $request = $this->createRequest('http://foo.com');
        $request->setBody(new StringBody('dummy body'));
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->expects($this->once())
            ->method('readFromStream')
            ->with($request->getBody()->readAsStream(), User::class)
            ->willThrowException(new SerializationException);
        $response = $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $request,
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(new RouteAction(ApiController::class, 'nullableObjectParameter', null), [], [])
            )
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('null', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithNullableObjectParameterWithoutBodyPassesNull(): void
    {
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->expects($this->never())
            ->method('readFromStream');
        $response = $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(new RouteAction(ApiController::class, 'nullableObjectParameter', null), [], [])
            )
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('null', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithNullableScalarParameterWithNoMatchingValuePassesNull(): void
    {
        $request = $this->createRequest('http://foo.com');
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $response = $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $request,
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(new RouteAction(ApiController::class, 'nullableScalarParameter', null), [], [])
            )
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('null', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithObjectParameterAndNoRequestBodyThrowsException(): void
    {
        $this->expectException(HttpException::class);
        $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                null,
                null,
                new MatchedRoute(new RouteAction(ApiController::class, 'objectParameter', null), [], [])
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
                $request,
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(new RouteAction(ApiController::class, 'objectParameter', null), [], [])
            )
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('id:123, email:foo@bar.com', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithScalarParameterAndNoMatchingVariableThrowsException(): void
    {
        $this->expectException(HttpException::class);
        $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                null,
                null,
                new MatchedRoute(new RouteAction(ApiController::class, 'stringParameter', null), [], [])
            )
        );
    }

    public function testInvokingMethodWithScalarParameterAndNoMatchingVariableUsesDefaultValueIfAvailable(): void
    {
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $response = $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(new RouteAction(ApiController::class, 'defaultValueParameter', null), [], [])
            )
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('bar', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithScalarParameterUsesMatchingQueryStringVariable(): void
    {
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $response = $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com/?foo=bar'),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(new RouteAction(ApiController::class, 'stringParameter', null), [], [])
            )
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('bar', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithScalarParameterUsesMatchingRouteVariableOverQueryStringVariable(): void
    {
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $response = $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com/?foo=baz'),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(new RouteAction(ApiController::class, 'stringParameter', null), ['foo' => 'dave'], [])
            )
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('dave', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithVoidReturnTypeReturnsNoContentResponse(): void
    {
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $response = $this->invoker->invokeRouteAction(
            new ControllerContext(
                $this->controller,
                $this->createRequest('http://foo.com'),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new ContentNegotiationResult($mediaTypeFormatter, null, null, null),
                new MatchedRoute(new RouteAction(ApiController::class, 'voidReturnType', null), [], [])
            )
        );
        $this->assertNull($response->getBody());
        $this->assertEquals(HttpStatusCodes::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testInvokingNonExistentMethodThrowsException(): void
    {
        $this->expectException(HttpException::class);
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
        $this->expectException(HttpException::class);
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
        $this->expectException(HttpException::class);
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
