<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests;

use Aphiria\Api\Application;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    private Application $app;
    /** @var IRequestHandler|MockObject */
    private IRequestHandler $router;
    private MiddlewareCollection $middleware;

    protected function setUp(): void
    {
        $this->router = $this->createMock(IRequestHandler::class);
        $this->middleware = new MiddlewareCollection();
        $this->app = new Application($this->router, $this->middleware);
    }

    public function testHandleWillSendRequestThroughMiddlewarePipeline(): void
    {
        $request = $this->createMock(IRequest::class);
        $middleware = $this->createMock(IMiddleware::class);
        $middleware->expects($this->once())
            ->method('handle')
            ->with($request, $this->router);
        $this->middleware->add($middleware);
        $this->app->handle($request);
    }

    public function testHandleWithNoMiddlewareStillSendsRequestToRouter(): void
    {
        $request = $this->createMock(IRequest::class);
        $response = $this->createMock(IResponse::class);
        $this->router->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->assertSame($response, $this->app->handle($request));
    }
}
