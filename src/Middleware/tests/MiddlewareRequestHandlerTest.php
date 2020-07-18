<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Tests;

use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewareRequestHandler;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MiddlewareRequestHandlerTest extends TestCase
{
    public function testHandlingRequestInvokesMiddlewareWithNextRequestHandler(): void
    {
        /** @var IMiddleware|MockObject $middleware */
        $middleware = $this->createMock(IMiddleware::class);
        /** @var IRequestHandler|MockObject $next */
        $next = $this->createMock(IRequestHandler::class);
        /** @var IRequest|MockObject $request */
        $request = $this->createMock(IRequest::class);
        /** @var IResponse|MockObject $response */
        $response = $this->createMock(IResponse::class);
        $middleware->expects($this->once())
            ->method('handle')
            ->with($request, $next)
            ->willReturn($response);

        $middlewareRequestHandler = new MiddlewareRequestHandler($middleware, $next);
        $this->assertSame($response, $middlewareRequestHandler->handle($request));
    }
}
