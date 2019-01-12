<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
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
use Opulence\Net\Http\ContentNegotiation\IContentNegotiator;
use Opulence\Net\Http\ContentNegotiation\INegotiatedResponseFactory;
use Opulence\Net\Http\HttpException;
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use Opulence\Net\Http\Request;
use Opulence\Net\Uri;
use Opulence\Routing\Matchers\RouteMatchingResult;
use Opulence\Routing\Route;
use Opulence\Routing\RouteAction;
use Opulence\Routing\UriTemplates\UriTemplate;
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

    public function setUp(): void
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
            $matchingResult = new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'stringParameter', null),
                    []
                ),
                [],
                []
            );
            $this->invoker->invokeRouteAction(
                [$this->controller, 'stringParameter'],
                $this->createMock(IHttpRequestMessage::class),
                $matchingResult
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
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(null, null, $closure),
                []
            ),
            [],
            []
        );
        $actualResponse = $this->invoker->invokeRouteAction($closure, $request, $matchingResult);
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
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(Controller::class, 'popo', null),
                []
            ),
            [],
            []
        );
        $actualResponse = $this->invoker->invokeRouteAction(
            [$this->controller, 'popo'],
            $request,
            $matchingResult
        );
        $this->assertSame($expectedResponse, $actualResponse);
    }

    public function testInvokingMethodThatThrowsExceptionThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(Controller::class, 'throwsException', null),
                []
            ),
            [],
            []
        );
        $this->invoker->invokeRouteAction(
            [$this->controller, 'throwsException'],
            $this->createRequestWithoutBody('http://foo.com'),
            $matchingResult
        );
    }

    public function testInvokingMethodWithNoParametersIsSuccessful(): void
    {
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(Controller::class, 'noParameters', null),
                []
            ),
            [],
            []
        );
        $response = $this->invoker->invokeRouteAction(
            [$this->controller, 'noParameters'],
            $this->createRequestWithoutBody('http://foo.com'),
            $matchingResult
        );
        $this->assertNotNull($response->getBody());
        $this->assertEquals('noParameters', $response->getBody()->readAsString());
    }

    public function testInvokingMethodWithVoidReturnTypeReturnsNoContentResponse(): void
    {
        $matchingResult = new RouteMatchingResult(
            new Route(
                new UriTemplate('foo'),
                new RouteAction(Controller::class, 'voidReturnType', null),
                []
            ),
            [],
            []
        );
        $response = $this->invoker->invokeRouteAction(
            [$this->controller, 'voidReturnType'],
            $this->createRequestWithoutBody('http://foo.com'),
            $matchingResult
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
            $matchingResult = new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'stringParameter', null),
                    []
                ),
                [],
                []
            );
            $this->invoker->invokeRouteAction(
                [$this->controller, 'stringParameter'],
                $this->createMock(IHttpRequestMessage::class),
                $matchingResult
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
            $matchingResult = new RouteMatchingResult(
                new Route(
                    new UriTemplate('foo'),
                    new RouteAction(Controller::class, 'stringParameter', null),
                    []
                ),
                [],
                []
            );
            $this->invoker->invokeRouteAction(
                [$this->controller, 'stringParameter'],
                $this->createMock(IHttpRequestMessage::class),
                $matchingResult
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
