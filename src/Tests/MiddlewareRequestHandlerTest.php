<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Middleware\Tests;

use Opulence\Middleware\IMiddleware;
use Opulence\Middleware\MiddlewareRequestHandler;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the middleware request handler
 */
class MiddlewareRequestHandlerTest extends TestCase
{
    public function testHandlingRequestInvokesMiddlewareWithNextRequestHandler(): void
    {
        /** @var IMiddleware|MockObject $middleware */
        $middleware = $this->createMock(IMiddleware::class);
        /** @var IRequestHandler|MockObject $next */
        $next = $this->createMock(IRequestHandler::class);
        /** @var IHttpRequestMessage|MockObject $request */
        $request = $this->createMock(IHttpRequestMessage::class);
        /** @var IHttpResponseMessage|MockObject $response */
        $response = $this->createMock(IHttpResponseMessage::class);
        $middleware->expects($this->once())
            ->method('handle')
            ->with($request, $next)
            ->willReturn($response);

        $middlewareRequestHandler = new MiddlewareRequestHandler($middleware, $next);
        $this->assertSame($response, $middlewareRequestHandler->handle($request));
    }
}
