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

use Aphiria\Api\Controllers\ControllerRequestHandler;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Tests\Controllers\Mocks\ControllerWithEndpoints;
use Aphiria\DependencyInjection\IServiceResolver;
use Aphiria\Net\Http\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the controller request handler
 */
class ControllerRequestHandlerTest extends TestCase
{
    /** @var IServiceResolver|MockObject */
    private IServiceResolver $serviceResolver;
    /** @var IRouteActionInvoker|MockObject */
    private IRouteActionInvoker $routeActionInvoker;
    /** @var IContentNegotiator|MockObject */
    private IContentNegotiator $contentNegotiator;

    protected function setUp(): void
    {
        $this->serviceResolver = $this->createMock(IServiceResolver::class);
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->routeActionInvoker = $this->createMock(IRouteActionInvoker::class);
    }

    public function testHandlingRequestSetsControllerProperties(): void
    {
        /** @var IHttpRequestMessage|MockObject $request */
        $request = $this->createMock(IHttpRequestMessage::class);
        /** @var ControllerWithEndpoints|MockObject $controller */
        $controller = $this->createMock(ControllerWithEndpoints::class);
        $controller->expects($this->once())
            ->method('setRequest')
            ->with($request);
        $controller->expects($this->once())
            ->method('setRequestParser');
        $controller->expects($this->once())
            ->method('setContentNegotiator')
            ->with($this->contentNegotiator);
        $controller->expects($this->once())
            ->method('setResponseFactory');
        $controllerCallable = [$controller, 'noParameters'];
        $this->routeActionInvoker->expects($this->once())
            ->method('invokeRouteAction')
            ->with($controllerCallable, $request, [])
            ->willReturn($this->createMock(IHttpResponseMessage::class));

        $requestHandler = new ControllerRequestHandler(
            $controller,
            $controllerCallable,
            [],
            $this->contentNegotiator,
            $this->routeActionInvoker
        );
        $requestHandler->handle($request);
    }

    public function testHandlingRequestReturnsResponseFromRouteInvoker(): void
    {
        /** @var IHttpRequestMessage|MockObject $request */
        $request = $this->createMock(IHttpRequestMessage::class);
        /** @var IHttpResponseMessage|MockObject $expectedResponse */
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        /** @var ControllerWithEndpoints|MockObject $controller */
        $controller = $this->createMock(ControllerWithEndpoints::class);
        $controllerCallable = [$controller, 'noParameters'];
        $this->routeActionInvoker->expects($this->once())
            ->method('invokeRouteAction')
            ->with($controllerCallable, $request, [])
            ->willReturn($expectedResponse);

        $requestHandler = new ControllerRequestHandler(
            $controller,
            $controllerCallable,
            [],
            $this->contentNegotiator,
            $this->routeActionInvoker
        );
        $this->assertSame($expectedResponse, $requestHandler->handle($request));
    }
}
