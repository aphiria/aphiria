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

use Aphiria\Api\Controllers\ControllerRequestHandler;
use Aphiria\Api\Controllers\IRouteActionInvoker;
use Aphiria\Api\Tests\Controllers\Mocks\ControllerWithEndpoints;
use Aphiria\Authentication\IUserAccessor;
use Aphiria\ContentNegotiation\IBodyDeserializer;
use Aphiria\ContentNegotiation\IContentNegotiator;
use Aphiria\ContentNegotiation\NegotiatedBodyDeserializer;
use Aphiria\Net\Http\Formatting\RequestParser;
use Aphiria\Net\Http\Formatting\ResponseFormatter;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IResponse;
use Aphiria\Net\Http\IResponseFactory;
use Closure;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ControllerRequestHandlerTest extends TestCase
{
    private IContentNegotiator&MockObject $contentNegotiator;
    private IRouteActionInvoker&MockObject $routeActionInvoker;
    private IUserAccessor&MockObject $userAccessor;

    protected function setUp(): void
    {
        $this->contentNegotiator = $this->createMock(IContentNegotiator::class);
        $this->routeActionInvoker = $this->createMock(IRouteActionInvoker::class);
        $this->userAccessor = $this->createMock(IUserAccessor::class);
    }

    public function testHandlingRequestReturnsResponseFromRouteInvoker(): void
    {
        /** @var IRequest&MockObject $request */
        $request = $this->createMock(IRequest::class);
        /** @var IResponse&MockObject $expectedResponse */
        $expectedResponse = $this->createMock(IResponse::class);
        /** @var ControllerWithEndpoints&MockObject $controller */
        $controller = $this->createMock(ControllerWithEndpoints::class);
        /** @psalm-suppress UndefinedMethod This method clearly does exist - bug */
        $controllerClosure = Closure::fromCallable([$controller, 'noParameters']);
        $this->routeActionInvoker->expects($this->once())
            ->method('invokeRouteAction')
            ->with($controllerClosure, $request, [])
            ->willReturn($expectedResponse);

        $requestHandler = new ControllerRequestHandler(
            $controller,
            $controllerClosure,
            [],
            $this->contentNegotiator,
            $this->routeActionInvoker
        );
        $this->assertSame($expectedResponse, $requestHandler->handle($request));
    }

    public function testHandlingRequestSetsControllerProperties(): void
    {
        /** @var IRequest&MockObject $request */
        $request = $this->createMock(IRequest::class);
        /** @var ControllerWithEndpoints&MockObject $controller */
        $controller = $this->createMock(ControllerWithEndpoints::class);
        /** @psalm-suppress UndefinedMethod This method clearly does exist - bug */
        $controllerClosure = Closure::fromCallable([$controller, 'noParameters']);
        $this->routeActionInvoker->expects($this->once())
            ->method('invokeRouteAction')
            ->with($controllerClosure, $request, [])
            ->willReturn($this->createMock(IResponse::class));

        $requestHandler = new ControllerRequestHandler(
            $controller,
            $controllerClosure,
            [],
            $this->contentNegotiator,
            $this->routeActionInvoker,
            $this->userAccessor
        );
        $requestHandler->handle($request);
        $this->assertSame($request, $controller->request);
        $this->assertInstanceOf(RequestParser::class, $controller->requestParser);
        $this->assertInstanceOf(IBodyDeserializer::class, $controller->bodyDeserializer);
        $this->assertInstanceOf(IResponseFactory::class, $controller->responseFactory);
        $this->assertInstanceOf(ResponseFormatter::class, $controller->responseFormatter);
        $this->assertInstanceOf(IUserAccessor::class, $controller->userAccessor);
    }
}
