<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\HttpException;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
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
    private IContentNegotiator&MockObject $contentNegotiator;
    private ControllerWithEndpoints $controller;
    private RouteActionInvoker $invoker;
    private IControllerParameterResolver&MockObject $parameterResolver;
    private IRequestBodyValidator&MockObject $requestBodyValidator;
    private IResponseFactory&MockObject $responseFactory;

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
                Closure::fromCallable([$this->controller, 'stringParameter']),
                $this->createMock(IRequest::class),
                []
            );
            $this->fail('Failed to assert that a 415 was thrown');
        } catch (HttpException $ex) {
            $this->assertSame(HttpStatusCode::UnsupportedMediaType, $ex->response->getStatusCode());
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
                Closure::fromCallable([$this->controller, 'stringParameter']),
                $this->createMock(IRequest::class),
                []
            );
            $this->fail('Failed to assert that a 400 was thrown');
        } catch (HttpException $ex) {
            $this->assertSame(HttpStatusCode::BadRequest, $ex->response->getStatusCode());
        }
    }

    public function testInvokingClosureReturnsResponseReturnedFromClosure(): void
    {
        $expectedResponse = $this->createMock(IResponse::class);
        $closure = function (int $foo) use ($expectedResponse): IResponse {
            $this->assertSame(123, $foo);

            return $expectedResponse;
        };
        /** @var IRequest&MockObject $request */
        $request = $this->createMock(IRequest::class);
        $this->parameterResolver->expects($this->once())
            ->method('resolveParameter')
            ->with($this->isInstanceOf(ReflectionParameter::class), $request)
            ->willReturn(123);
        $actualResponse = $this->invoker->invokeRouteAction($closure, $request, []);
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testInvokingMethodThatReturnsPopoCreatesOkResponseFromReturnValue(): void
    {
        /** @var IRequest&MockObject $request */
        $request = $this->createMock(IRequest::class);
        $expectedResponse = $this->createMock(IResponse::class);
        $this->responseFactory->method('createResponse')
            ->with($request, HttpStatusCode::Ok, null, $this->callback(fn (mixed $actionResult): bool => $actionResult instanceof User))
            ->willReturn($expectedResponse);
        $actualResponse = $this->invoker->invokeRouteAction(
            Closure::fromCallable([$this->controller, 'popo']),
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
            Closure::fromCallable([$this->controller, 'throwsException']),
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
            Closure::fromCallable([$this->controller, 'objectParameter']),
            $request,
            []
        );
    }

    public function testInvokingMethodWithNoParametersIsSuccessful(): void
    {
        $response = $this->invoker->invokeRouteAction(
            Closure::fromCallable([$this->controller, 'noParameters']),
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
        $this->assertNotNull($response->getBody());
        $this->assertSame('noParameters', $response->getBody()?->readAsString());
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
            Closure::fromCallable([$this->controller, 'objectParameter']),
            $request,
            []
        );
    }

    public function testInvokingMethodWithVoidReturnTypeReturnsNoContentResponse(): void
    {
        $response = $this->invoker->invokeRouteAction(
            Closure::fromCallable([$this->controller, 'voidReturnType']),
            $this->createRequestWithoutBody('http://foo.com'),
            []
        );
        $this->assertNull($response->getBody());
        $this->assertSame(HttpStatusCode::NoContent, $response->getStatusCode());
    }

    public function testInvokingRouteActionWithUnreflectableRouteActionDelegateThrowsException(): void
    {
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Failed to reflect controller');
        $routeActionInvoker = new class () extends RouteActionInvoker {
            protected function reflectRouteActionDelegate(Closure $routeActionDelegate): ReflectionFunctionAbstract
            {
                throw new ReflectionException();
            }
        };
        $routeActionInvoker->invokeRouteAction(fn () => null, $this->createRequestWithoutBody('http://example.com'), []);
    }

    public function testInvokingRouteActionWithUnreflectableStaticRouteActionDelegateThrowsException(): void
    {
        $this->expectException(HttpException::class);
        $controller = new class () extends Controller {
            public static function foo(): void
            {
            }
        };
        $this->expectExceptionMessage('Failed to reflect controller');
        $routeActionInvoker = new class () extends RouteActionInvoker {
            protected function reflectRouteActionDelegate(Closure $routeActionDelegate): ReflectionFunctionAbstract
            {
                throw new ReflectionException();
            }
        };
        $routeActionInvoker->invokeRouteAction(
            Closure::fromCallable([$controller::class, 'foo']),
            $this->createRequestWithoutBody('http://example.com'),
            []
        );
    }

    public function testMissingControllerParameterValueExceptionIsRethrownAsHttpException(): void
    {
        try {
            $this->parameterResolver->expects($this->once())
                ->method('resolveParameter')
                ->with($this->anything(), $this->anything())
                ->willThrowException(new MissingControllerParameterValueException());
            $this->invoker->invokeRouteAction(
                Closure::fromCallable([$this->controller, 'stringParameter']),
                $this->createMock(IRequest::class),
                []
            );
            $this->fail('Failed to assert that a 400 was thrown');
        } catch (HttpException $ex) {
            $this->assertSame(HttpStatusCode::BadRequest, $ex->response->getStatusCode());
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
            Closure::fromCallable([$this->controller, 'objectParameter']),
            $request,
            []
        );
        $this->assertEquals($expectedUser, $request->getProperties()->get('__APHIRIA_PARSED_BODY'));
    }

    public function testRequestBodyDeserializationExceptionIsRethrownAsHttpException(): void
    {
        try {
            $this->parameterResolver->expects($this->once())
                ->method('resolveParameter')
                ->with($this->anything(), $this->anything())
                ->willThrowException(new RequestBodyDeserializationException());
            $this->invoker->invokeRouteAction(
                Closure::fromCallable([$this->controller, 'stringParameter']),
                $this->createMock(IRequest::class),
                []
            );
            $this->fail('Failed to assert that a 522 was thrown');
        } catch (HttpException $ex) {
            $this->assertSame(HttpStatusCode::UnprocessableEntity, $ex->response->getStatusCode());
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
