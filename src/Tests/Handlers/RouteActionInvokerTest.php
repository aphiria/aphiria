<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Handlers;

use Opulence\Api\Handlers\ControllerParameterResolver;
use Opulence\Api\Handlers\FailedRequestContentNegotiationException;
use Opulence\Api\Handlers\IControllerParameterResolver;
use Opulence\Api\Handlers\MissingControllerParameterValueException;
use Opulence\Api\Handlers\RequestBodyDeserializationException;
use Opulence\Api\Handlers\RouteActionInvoker;
use Opulence\Api\Tests\Handlers\Mocks\Controller;
use Opulence\Api\Tests\Handlers\Mocks\User;
use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Request;
use Opulence\Net\Uri;
use Opulence\Routing\Matchers\MatchedRoute;
use Opulence\Routing\RouteAction;
use ReflectionParameter;
use RuntimeException;

/**
 * Tests the route action invoker
 */
class RouteActionInvokerTest extends \PHPUnit\Framework\TestCase
{
    /** @var RouteActionInvoker The invoker to use in tests */
    private $invoker;
    /** @var ControllerParameterResolver|\PHPUnit_Framework_MockObject_MockObject The controller parameter resolver to use in tests */
    private $parameterResolver;
    /** @var IContentNegotiator|\PHPUnit_Framework_MockObject_MockObject The content negotiator to use */
    private $contentNegotiator;
    /** @var Controller The controller to use in tests */
    private $controller;

    public function setUp(): void
    {
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->parameterResolver = $this->createMock(IControllerParameterResolver::class);
        $this->invoker = new RouteActionInvoker($this->contentNegotiator, null, $this->parameterResolver);
        $this->controller = new Controller();
    }

    public function testFailedRequestContentNegotiationExceptionIsRethrownAsHttpException(): void
    {
        try {
            $this->parameterResolver->expects($this->once())
                ->method('resolveParameter')
                ->with($this->anything(), $this->anything())
                ->willThrowException(new FailedRequestContentNegotiationException);
            $matchedRoute = new MatchedRoute(new RouteAction(Controller::class, 'stringParameter', null), [], []);
            $this->invoker->invokeRouteAction(
                [$this->controller, 'stringParameter'],
                $this->createMock(IHttpRequestMessage::class),
                $matchedRoute
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
        $request = $this->createMock(IHttpRequestMessage::class);
        $this->parameterResolver->expects($this->once())
            ->method('resolveParameter')
            ->with($this->isInstanceOf(ReflectionParameter::class), $request)
            ->willReturn(123);
        $matchedRoute = new MatchedRoute(new RouteAction(null, null, $closure), [], []);
        $actualResponse = $this->invoker->invokeRouteAction($closure, $request, $matchedRoute);
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testInvokingMethodThatReturnsPopoCreatesOkResponseFromReturnValue(): void
    {
        $request = $this->createMock(IHttpRequestMessage::class);
        /** @var IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject $mediaTypeFormatter */
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->expects($this->once())
            ->method('writeToStream')
            ->with($this->isInstanceOf(User::class), $this->isInstanceOf(IStream::class));
        $this->contentNegotiator->expects($this->once())
            ->method('negotiateResponseContent')
            ->with(User::class, $request)
            ->willReturn(new ContentNegotiationResult($mediaTypeFormatter, null, null, null));
        $matchedRoute = new MatchedRoute(new RouteAction(Controller::class, 'popo', null), [], []);
        $response = $this->invoker->invokeRouteAction(
            [$this->controller, 'popo'],
            $request,
            $matchedRoute
        );
        $this->assertEquals(HttpStatusCodes::HTTP_OK, $response->getStatusCode());
        /**
         *  Note: I cannot (easily) test what the body is because I cannot set up my formatter mock to write
         *  specific serialized POPO contents to the body
         */
    }

    public function testInvokingMethodThatThrowsExceptionThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $matchedRoute = new MatchedRoute(new RouteAction(Controller::class, 'throwsException', null), [], []);
        $this->invoker->invokeRouteAction(
            [$this->controller, 'throwsException'],
            $this->createRequestWithoutBody('http://foo.com'),
            $matchedRoute
        );
    }

    public function testInvokingMethodWithNoParametersIsSuccessful(): void
    {
        $matchedRoute = new MatchedRoute(new RouteAction(Controller::class, 'noParameters', null), [], []);
        $response = $this->invoker->invokeRouteAction(
            [$this->controller, 'noParameters'],
            $this->createRequestWithoutBody('http://foo.com'),
            $matchedRoute
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('noParameters', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithVoidReturnTypeReturnsNoContentResponse(): void
    {
        $matchedRoute = new MatchedRoute(new RouteAction(Controller::class, 'voidReturnType', null), [], []);
        $response = $this->invoker->invokeRouteAction(
            [$this->controller, 'voidReturnType'],
            $this->createRequestWithoutBody('http://foo.com'),
            $matchedRoute
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
            $matchedRoute = new MatchedRoute(new RouteAction(Controller::class, 'stringParameter', null), [], []);
            $this->invoker->invokeRouteAction(
                [$this->controller, 'stringParameter'],
                $this->createMock(IHttpRequestMessage::class),
                $matchedRoute
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
            $matchedRoute = new MatchedRoute(new RouteAction(Controller::class, 'stringParameter', null), [], []);
            $this->invoker->invokeRouteAction(
                [$this->controller, 'stringParameter'],
                $this->createMock(IHttpRequestMessage::class),
                $matchedRoute
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
