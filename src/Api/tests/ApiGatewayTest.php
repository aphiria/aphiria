<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests;

use Aphiria\Api\ApiGateway;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApiGatewayTest extends TestCase
{
    private ApiGateway $apiGateway;
    private MiddlewareCollection $middleware;
    private IRequestHandler&MockObject $router;

    protected function setUp(): void
    {
        $this->router = $this->createMock(IRequestHandler::class);
        $this->middleware = new MiddlewareCollection();
        $this->apiGateway = new ApiGateway($this->router, $this->middleware);
    }

    public function testHandleWillSendRequestThroughMiddlewarePipeline(): void
    {
        $request = $this->createMock(IRequest::class);
        $middleware = $this->createMock(IMiddleware::class);
        $middleware->expects($this->once())
            ->method('handle')
            ->with($request, $this->router);
        $this->middleware->add($middleware);
        $this->apiGateway->handle($request);
    }

    public function testHandleWithNoMiddlewareStillSendsRequestToRouter(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $this->router->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->assertSame($response, $this->apiGateway->handle($request));
    }
}
