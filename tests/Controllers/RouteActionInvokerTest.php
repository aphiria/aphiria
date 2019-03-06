<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

namespace Aphiria\Api\Tests\Controllers;

use Aphiria\Api\Controllers\ControllerParameterResolver;
use Aphiria\Api\Controllers\FailedRequestContentNegotiationException;
use Aphiria\Api\Controllers\IControllerParameterResolver;
use Aphiria\Api\Controllers\MissingControllerParameterValueException;
use Aphiria\Api\Controllers\RequestBodyDeserializationException;
use Aphiria\Api\Controllers\RouteActionInvoker;
use Aphiria\Api\Tests\Controllers\Mocks\Controller;
use Aphiria\Api\Tests\Controllers\Mocks\User;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Uri;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionParameter;
use RuntimeException;

/**
 * Tests the route action invoker
 */
class RouteActionInvokerTest extends TestCase
{
    /** @var RouteActionInvoker The invoker to use in tests */
    private $invoker;
    /** @var ControllerParameterResolver|MockObject The controller parameter resolver to use in tests */
    private $parameterResolver;
    /** @var IContentNegotiator|MockObject The content negotiator to use */
    private $contentNegotiator;
    /** @var INegotiatedResponseFactory|MockObject The negotiated response factory */
    private $negotiatedResponseFactory;
    /** @var Controller The controller to use in tests */
    private $controller;

    protected function setUp(): void
    {
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->negotiatedResponseFactory = $this->createMock(INegotiatedResponseFactory::class);
        $this->parameterResolver = $this->createMock(IControllerParameterResolver::class);
        $this->invoker = new RouteActionInvoker(
            $this->contentNegotiator,
            $this->negotiatedResponseFactory,
            $this->parameterResolver
        );
        $this->controller = new Controller();
    }

    public function testFailedRequestContentNegotiationExceptionIsRethrownAsHttpException(): void
    {
        try {
            $this->parameterResolver->expects($this->once())
                ->method('resolveParameter')
                ->with($this->anything(), $this->anything())
                ->willThrowException(new FailedRequestContentNegotiationException);
            $this->invoker->invokeRouteAction(
                [$this->controller, 'stringParameter'],
                $this->createMock(IHttpRequestMessage::class),
                []
            );
            $this->fail('Failed to assert that a 415 was thrown');
        } catch (HttpException $ex) {
            $this->assertEquals(HttpStatusCodes::HTTP_UNSUPPORTED_MEDIA_TYPE, $ex->getResponse()->getStatusCode());
        }
    }

    public function testInvokingClosureReturnsResponseReturnedFromClosure(): void
    {
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $closure = function (int $foo) use ($expectedResponse) {
            $this->assertEquals(123, $foo);

            return $expectedResponse;
        };
        /** @var IHttpRequestMessage|MockObject $request */
        $request = $this->createMock(IHttpRequestMessage::class);
        $this->parameterResolver->expects($this->once())
            ->method('resolveParameter')
            ->with($this->isInstanceOf(ReflectionParameter::class), $request)
            ->willReturn(123);
        $actualResponse = $this->invoker->invokeRouteAction($closure, $request, []);
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testInvokingMethodThatReturnsPopoCreatesOkResponseFromReturnValue(): void
    {
        /** @var IHttpRequestMessage|MockObject $request */
        $request = $this->createMock(IHttpRequestMessage::class);
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $this->negotiatedResponseFactory->method('createResponse')
            ->with($request, HttpStatusCodes::HTTP_OK, null, $this->callback(function ($actionResult) {
                return $actionResult instanceof User;
            }))
            ->willReturn($expectedResponse);
        $actualResponse = $this->invoker->invokeRouteAction(
            [$this->controller, 'popo'],
            $request,
            []
        );
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testInvokingMethodThatThrowsExceptionThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Testing controller method that throws exception');
        $this->invoker->invokeRouteAction(
            [$this->controller, 'throwsException'],
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
    }

    public function testInvokingMethodWithNoParametersIsSuccessful(): void
    {
        $response = $this->invoker->invokeRouteAction(
            [$this->controller, 'noParameters'],
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('noParameters', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithVoidReturnTypeReturnsNoContentResponse(): void
    {
        $response = $this->invoker->invokeRouteAction(
            [$this->controller, 'voidReturnType'],
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
        $this->assertNull($response->getBody());
        $this->assertEquals(HttpStatusCodes::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testMissingControllerParameterValueExceptionIsRethrownAsHttpException(): void
    {
        try {
            $this->parameterResolver->expects($this->once())
                ->method('resolveParameter')
                ->with($this->anything(), $this->anything())
                ->willThrowException(new MissingControllerParameterValueException);
            $this->invoker->invokeRouteAction(
                [$this->controller, 'stringParameter'],
                $this->createMock(IHttpRequestMessage::class),
                []
            );
            $this->fail('Failed to assert that a 400 was thrown');
        } catch (HttpException $ex) {
            $this->assertEquals(HttpStatusCodes::HTTP_BAD_REQUEST, $ex->getResponse()->getStatusCode());
        }
    }

    public function testRequestBodyDeserializationExceptionIsRethrownAsHttpException(): void
    {
        try {
            $this->parameterResolver->expects($this->once())
                ->method('resolveParameter')
                ->with($this->anything(), $this->anything())
                ->willThrowException(new RequestBodyDeserializationException);
            $this->invoker->invokeRouteAction(
                [$this->controller, 'stringParameter'],
                $this->createMock(IHttpRequestMessage::class),
                []
            );
            $this->fail('Failed to assert that a 522 was thrown');
        } catch (HttpException $ex) {
            $this->assertEquals(HttpStatusCodes::HTTP_UNPROCESSABLE_ENTITY, $ex->getResponse()->getStatusCode());
        }
    }

    /**
     * Creates a request with the input URI and no body
     *
     * @param string $uri The URI to use
     * @return Request The request
     */
    private function createRequestWithoutBody(string $uri): Request
    {
        return new Request('GET', new Uri($uri));
    }
}
