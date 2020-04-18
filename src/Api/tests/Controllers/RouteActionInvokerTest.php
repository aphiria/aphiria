<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Controllers;

use Aphiria\Api\Controllers\Controller;
use Aphiria\Api\Controllers\FailedRequestContentNegotiationException;
use Aphiria\Api\Controllers\FailedScalarParameterConversionException;
use Aphiria\Api\Controllers\IControllerParameterResolver;
use Aphiria\Api\Controllers\MissingControllerParameterValueException;
use Aphiria\Api\Controllers\RequestBodyDeserializationException;
use Aphiria\Api\Controllers\RouteActionInvoker;
use Aphiria\Api\Tests\Controllers\Mocks\ControllerWithEndpoints;
use Aphiria\Api\Tests\Controllers\Mocks\User;
use Aphiria\Api\Validation\InvalidRequestBodyException;
use Aphiria\Api\Validation\IRequestBodyValidator;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\IResponseFactory;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Uri;
use Closure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionParameter;
use RuntimeException;

class RouteActionInvokerTest extends TestCase
{
    /** @var IRequestBodyValidator|MockObject */
    private IRequestBodyValidator $requestBodyValidator;
    private RouteActionInvoker $invoker;
    /** @var IControllerParameterResolver|MockObject */
    private IControllerParameterResolver $parameterResolver;
    /** @var IContentNegotiator|MockObject */
    private IContentNegotiator $contentNegotiator;
    /** @var IResponseFactory|MockObject */
    private IResponseFactory $responseFactory;
    private ControllerWithEndpoints $controller;

    protected function setUp(): void
    {
        $this->requestBodyValidator = $this->createMock(IRequestBodyValidator::class);
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->responseFactory = $this->createMock(IResponseFactory::class);
        $this->parameterResolver = $this->createMock(IControllerParameterResolver::class);
        $this->invoker = new RouteActionInvoker(
            $this->contentNegotiator,
            $this->requestBodyValidator,
            $this->responseFactory,
            $this->parameterResolver
        );
        $this->controller = new ControllerWithEndpoints();
    }

    public function testFailedRequestContentNegotiationExceptionIsRethrownAsHttpException(): void
    {
        try {
            $this->parameterResolver->expects($this->once())
                ->method('resolveParameter')
                ->with($this->anything(), $this->anything())
                ->willThrowException(new FailedRequestContentNegotiationException());
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

    public function testFailedScalarParameterConversionExceptionIsRethrownAsHttpException(): void
    {
        try {
            $this->parameterResolver->expects($this->once())
                ->method('resolveParameter')
                ->with($this->anything(), $this->anything())
                ->willThrowException(new FailedScalarParameterConversionException());
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
        $this->responseFactory->method('createResponse')
            ->with($request, HttpStatusCodes::HTTP_OK, null, $this->callback(fn ($actionResult) => $actionResult instanceof User))
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

    public function testInvokingMethodWithInvalidRequestBodyThrowsException(): void
    {
        $this->expectException(InvalidRequestBodyException::class);
        $request = $this->createRequestWithoutBody('http://foo.com');
        $expectedUser = new User(123, 'foo@bar.com');
        $this->parameterResolver->expects($this->once())
            ->method('resolveParameter')
            ->with($this->anything(), $this->anything())
            ->willReturn($expectedUser);
        $this->requestBodyValidator->expects($this->once())
            ->method('validate')
            ->with($request, $expectedUser)
            ->willThrowException(new InvalidRequestBodyException(['error']));
        $this->invoker->invokeRouteAction(
            [$this->controller, 'objectParameter'],
            $request,
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

    public function testInvokingMethodWithValidObjectParameterDoesNotThrowException(): void
    {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $expectedUser = new User(123, 'foo@bar.com');
        $this->parameterResolver->expects($this->once())
            ->method('resolveParameter')
            ->with($this->anything(), $this->anything())
            ->willReturn($expectedUser);
        $this->requestBodyValidator->expects($this->once())
            ->method('validate')
            ->with($request, $expectedUser);
        $this->invoker->invokeRouteAction(
            [$this->controller, 'objectParameter'],
            $request,
            []
        );
        // Dummy assertion
        $this->assertTrue(true);
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

    public function testInvokingRouteActionWithUnreflectableRouteActionDelegateThrowsException(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Reflection failed for ' . Closure::class);
        $routeActionInvoker = new class() extends RouteActionInvoker {
            protected function reflectRouteActionDelegate(callable $routeActionDelegate): ReflectionFunctionAbstract
            {
                throw new ReflectionException();
            }
        };
        $routeActionInvoker->invokeRouteAction(fn () => null, $this->createRequestWithoutBody('http://example.com'), []);
    }

    public function testInvokingRouteActionWithUnreflectableStaticRouteActionDelegateThrowsException(): void
    {
        $this->expectException(HttpException::class);
        $controller = new class() extends Controller {
            public static function foo(): void
            {
            }
        };
        $this->expectExceptionMessage('Reflection failed for ' . \get_class($controller) . '::foo');
        $routeActionInvoker = new class() extends RouteActionInvoker {
            protected function reflectRouteActionDelegate(callable $routeActionDelegate): ReflectionFunctionAbstract
            {
                throw new ReflectionException();
            }
        };
        $routeActionInvoker->invokeRouteAction([\get_class($controller), 'foo'], $this->createRequestWithoutBody('http://example.com'), []);
    }

    public function testMissingControllerParameterValueExceptionIsRethrownAsHttpException(): void
    {
        try {
            $this->parameterResolver->expects($this->once())
                ->method('resolveParameter')
                ->with($this->anything(), $this->anything())
                ->willThrowException(new MissingControllerParameterValueException());
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

    public function testParsedBodyIsStoredInRequestProperty(): void
    {
        $request = $this->createRequestWithoutBody('http://foo.com');
        $expectedUser = new User(123, 'foo@bar.com');
        $this->parameterResolver->expects($this->once())
            ->method('resolveParameter')
            ->with($this->anything(), $this->anything())
            ->willReturn($expectedUser);
        $this->invoker->invokeRouteAction(
            [$this->controller, 'objectParameter'],
            $request,
            []
        );
        $this->assertEquals($expectedUser, $request->getProperties()->get('__APHIRIA_PARSED_BODY'));
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testRequestBodyDeserializationExceptionIsRethrownAsHttpException(): void
    {
        try {
            $this->parameterResolver->expects($this->once())
                ->method('resolveParameter')
                ->with($this->anything(), $this->anything())
                ->willThrowException(new RequestBodyDeserializationException());
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
