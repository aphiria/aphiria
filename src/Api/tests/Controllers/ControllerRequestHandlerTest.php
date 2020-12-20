<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Controllers;

use Aphiria\Api\Controllers\ControllerRequestHandler;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Tests\Controllers\Mocks\ControllerWithEndpoints;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ControllerRequestHandlerTest extends TestCase
{
    private IRouteActionInvoker|MockObject $routeActionInvoker;
    private IContentNegotiator|MockObject $contentNegotiator;

    protected function setUp(): void
    {
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->routeActionInvoker = $this->createMock(IRouteActionInvoker::class);
    }

    public function testHandlingRequestSetsControllerProperties(): void
    {
        /** @var IRequest|MockObject $request */
        $request = $this->createMock(IRequest::class);
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
            ->willReturn($this->createMock(IResponse::class));

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
        /** @var IRequest|MockObject $request */
        $request = $this->createMock(IRequest::class);
        /** @var IResponse|MockObject $expectedResponse */
        $expectedResponse = $this->createMock(IResponse::class);
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
