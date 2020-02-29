<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests;

use Aphiria\Api\App;
use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareCollection;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the app
 */
class AppTest extends TestCase
{
    private App $app;
    /** @var IRequestHandler|MockObject */
    private IRequestHandler $router;
    private MiddlewareCollection $middleware;

    protected function setUp(): void
    {
        $this->router = $this->createMock(IRequestHandler::class);
        $this->middleware = new MiddlewareCollection();
        $this->app = new App($this->router, $this->middleware);
    }

    public function testHandleWillSendRequestThroughMiddlewarePipeline(): void
    {
        $request = $this->createMock(IHttpRequestMessage::class);
        $middleware = $this->createMock(IMiddleware::class);
        $middleware->expects($this->once())
            ->method('handle')
            ->with($request, $this->router);
        $this->middleware->add($middleware);
        $this->app->handle($request);
    }

    public function testHandleWithNoMiddlewareStillSendsRequestToRouter(): void
    {
        $request = $this->createMock(IHttpRequestMessage::class);
        $response = $this->createMock(IHttpResponseMessage::class);
        $this->router->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);
        $this->assertSame($response, $this->app->handle($request));
    }
}
