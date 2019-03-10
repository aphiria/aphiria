<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Controllers;

use Aphiria\Api\Controllers\ControllerRequestHandler;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\IDependencyResolver;
use Aphiria\Api\Tests\Controllers\Mocks\Controller;
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
    /** @var IDependencyResolver|MockObject */
    private $dependencyResolver;
    /** @var IRouteActionInvoker|MockObject */
    private $routeActionInvoker;
    /** @var IContentNegotiator|MockObject */
    private $contentNegotiator;

    protected function setUp(): void
    {
        $this->dependencyResolver = $this->createMock(IDependencyResolver::class);
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->routeActionInvoker = $this->createMock(IRouteActionInvoker::class);
    }

    public function testHandlingRequestSetsControllerProperties(): void
    {
        /** @var IHttpRequestMessage|MockObject $request */
        $request = $this->createMock(IHttpRequestMessage::class);
        /** @var Controller|MockObject $controller */
        $controller = $this->createMock(Controller::class);
        $controller->expects($this->once())
            ->method('setRequest')
            ->with($request);
        $controller->expects($this->once())
            ->method('setRequestParser');
        $controller->expects($this->once())
            ->method('setContentNegotiator')
            ->with($this->contentNegotiator);
        $controller->expects($this->once())
            ->method('setNegotiatedResponseFactory');
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
        /** @var Controller|MockObject $controller */
        $controller = $this->createMock(Controller::class);
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
