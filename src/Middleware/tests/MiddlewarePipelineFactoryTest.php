<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Middleware\Tests;

use Aphiria\Middleware\IMiddleware;
use Aphiria\Middleware\MiddlewarePipelineFactory;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MiddlewarePipelineFactoryTest extends TestCase
{
    private MiddlewarePipelineFactory $pipelineFactory;

    public function setUp(): void
    {
        $this->pipelineFactory = new MiddlewarePipelineFactory();
    }

    public function testCreatingPipelineWithMultipleMiddlewareReturnsHandlerWithThoseMiddleware(): void
    {
        // We cannot test this directly because the middleware is internal to the request handler
        // So, we must test it by trying to execute the pipeline
        /** @var IRequest&MockObject $request */
        $request = $this->createMock(IRequest::class);
        /** @var IResponse&MockObject $response */
        $response = $this->createMock(IResponse::class);
        /** @var IRequestHandler&MockObject $controllerHandler */
        $controllerHandler = $this->createMock(IRequestHandler::class);
        /** @var IMiddleware&MockObject $middleware1 */
        $middleware1 = $this->createMock(IMiddleware::class);
        $middleware1->expects($this->once())
            ->method('handle')
            ->with($request, $this->callback(function (IRequestHandler $handler) use ($controllerHandler): bool {
                // Next handler will be the second middleware's handler, although there's no way to test that directly
                return $handler !== $controllerHandler;
            }))
            ->willReturn($response);
        /** @var IMiddleware&MockObject $middleware2 */
        $middleware2 = $this->createMock(IMiddleware::class);

        /** @psalm-suppress InvalidArgument Psalm doesn't handle union types yet - bug */
        $pipeline = $this->pipelineFactory->createPipeline([$middleware1, $middleware2], $controllerHandler);
        $this->assertSame($response, $pipeline->handle($request));
    }

    public function testCreatingPipelineWithNoMiddlewareJustReturnsControllerHandler(): void
    {
        /** @var IRequestHandler&MockObject $controllerHandler */
        $controllerHandler = $this->createMock(IRequestHandler::class);
        $this->assertSame($controllerHandler, $this->pipelineFactory->createPipeline([], $controllerHandler));
    }

    public function testCreatingPipelineWithOneMiddlewareReturnsHandlerWithThatMiddleware(): void
    {
        // We cannot test this directly because the middleware is internal to the request handler
        // So, we must test it by trying to execute the pipeline
        /** @var IRequest&MockObject $request */
        $request = $this->createMock(IRequest::class);
        /** @var IResponse&MockObject $response */
        $response = $this->createMock(IResponse::class);
        /** @var IRequestHandler&MockObject $controllerHandler */
        $controllerHandler = $this->createMock(IRequestHandler::class);
        /** @var IMiddleware&MockObject $middleware */
        $middleware = $this->createMock(IMiddleware::class);
        $middleware->expects($this->once())
            ->method('handle')
            ->with($request, $controllerHandler)
            ->willReturn($response);

        /** @psalm-suppress InvalidArgument Psalm doesn't handle union types yet - bug */
        $pipeline = $this->pipelineFactory->createPipeline([$middleware], $controllerHandler);
        $this->assertSame($response, $pipeline->handle($request));
    }
}
