<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Api\Exceptions;

use Aphiria\Exceptions\LogLevelFactory;
use Aphiria\Framework\Api\Exceptions\ExceptionHandler;
use Aphiria\Framework\Api\Exceptions\IApiExceptionRenderer;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\IRequestHandler;
use Aphiria\Net\Http\IResponse;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class ExceptionHandlerTest extends TestCase
{
    private IApiExceptionRenderer&MockObject $exceptionRenderer;
    private LoggerInterface&MockObject $logger;
    private LogLevelFactory $logLevelFactory;
    private ExceptionHandler $exceptionHandler;

    protected function setUp(): void
    {
        $this->exceptionRenderer = $this->createMock(IApiExceptionRenderer::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->logLevelFactory = new LogLevelFactory();
        $this->exceptionHandler = new ExceptionHandler($this->exceptionRenderer, $this->logger, $this->logLevelFactory);
    }

    public function testExceptionIsLoggedAndResponseIsCreatedOnException(): void
    {
        $request = $this->createMock(IRequest::class);
        $expectedResponse = $this->createMock(IResponse::class);
        $next = $this->createMock(IRequestHandler::class);
        $expectedException = new Exception();
        $next->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willThrowException($expectedException);
        $this->exceptionRenderer->method('setRequest')
            ->with($request);
        $this->exceptionRenderer->method('createResponse')
            ->willReturn($expectedResponse);
        $this->logLevelFactory->registerLogLevelFactory(Exception::class, fn (Exception $ex) => LogLevel::EMERGENCY);
        $this->logger->expects($this->once())
            ->method('emergency')
            ->with($expectedException);
        $this->assertSame($expectedResponse, $this->exceptionHandler->handle($request, $next));
    }

    public function testResponseIsReturnedFromNextRequestHandlerWhenThereIsNoException(): void
    {
        $request = $this->createMock(IRequest::class);
        $expectedResponse = $this->createMock(IResponse::class);
        $next = $this->createMock(IRequestHandler::class);
        $next->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->exceptionHandler->handle($request, $next));
    }
}
