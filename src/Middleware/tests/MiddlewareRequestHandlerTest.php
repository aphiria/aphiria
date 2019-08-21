<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/middleware/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Tests;

use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareRequestHandler;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
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
