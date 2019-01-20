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
use Opulence\Middleware\MiddlewarePipelineFactory;
use Opulence\Net\Http\Handlers\IRequestHandler;
use Opulence\Net\Http\IHttpRequestMessage;
use Opulence\Net\Http\IHttpResponseMessage;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the middleware pipeline factory
 */
class MiddlewarePipelineFactoryTest extends TestCase
{
    /** @var MiddlewarePipelineFactory */
    private $pipelineFactory;

    public function setUp(): void
    {
        $this->pipelineFactory = new MiddlewarePipelineFactory();
    }

    public function testCreatingPipelineWithMultipleMiddlewareReturnsHandlerWithThoseMiddleware(): void
    {
        // We cannot test this directly because the middleware is internal to the request handler
        // So, we must test it by trying to execute the pipeline
        /** @var IHttpRequestMessage|MockObject $request */
        $request = $this->createMock(IHttpRequestMessage::class);
        /** @var IHttpResponseMessage|MockObject $response */
        $response = $this->createMock(IHttpResponseMessage::class);
        /** @var IRequestHandler|MockObject $controllerHandler */
        $controllerHandler = $this->createMock(IRequestHandler::class);
        /** @var IMiddleware|MockObject $middleware1 */
        $middleware1 = $this->createMock(IMiddleware::class);
        $middleware1->expects($this->once())
            ->method('handle')
            ->with($request, $this->callback(function ($handler) use ($controllerHandler) {
                // Next handler will be the second middleware's handler, although there's no way to test that directly
                return $handler !== $controllerHandler;
            }))
            ->willReturn($response);
        /** @var IMiddleware|MockObject $middleware2 */
        $middleware2 = $this->createMock(IMiddleware::class);

        $pipeline = $this->pipelineFactory->createPipeline([$middleware1, $middleware2], $controllerHandler);
        $this->assertSame($response, $pipeline->handle($request));
    }

    public function testCreatingPipelineWithNoMiddlewareJustReturnsControllerHandler(): void
    {
        /** @var IRequestHandler|MockObject $controllerHandler */
        $controllerHandler = $this->createMock(IRequestHandler::class);
        $this->assertSame($controllerHandler, $this->pipelineFactory->createPipeline([], $controllerHandler));
    }

    public function testCreatingPipelineWithOneMiddlewareReturnsHandlerWithThatMiddleware(): void
    {
        // We cannot test this directly because the middleware is internal to the request handler
        // So, we must test it by trying to execute the pipeline
        /** @var IHttpRequestMessage|MockObject $request */
        $request = $this->createMock(IHttpRequestMessage::class);
        /** @var IHttpResponseMessage|MockObject $response */
        $response = $this->createMock(IHttpResponseMessage::class);
        /** @var IRequestHandler|MockObject $controllerHandler */
        $controllerHandler = $this->createMock(IRequestHandler::class);
        /** @var IMiddleware|MockObject $middleware */
        $middleware = $this->createMock(IMiddleware::class);
        $middleware->expects($this->once())
            ->method('handle')
            ->with($request, $controllerHandler)
            ->willReturn($response);

        $pipeline = $this->pipelineFactory->createPipeline([$middleware], $controllerHandler);
        $this->assertSame($response, $pipeline->handle($request));
    }
}
