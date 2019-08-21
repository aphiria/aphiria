<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/exceptions/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Exceptions;

use Aphiria\Exceptions\IExceptionLogger;
use Aphiria\Exceptions\IExceptionResponseFactory;
use Aphiria\Exceptions\Middleware\ExceptionHandler;
use Aphiria\Net\Http\Handlers\IRequestHandler;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the exception handler middleware
 */
class ExceptionHandlerTest extends TestCase
{
    private ExceptionHandler $middleware;
    /** @var IExceptionLogger|MockObject */
    private IExceptionLogger $exceptionLogger;
    /** @var IExceptionResponseFactory|MockObject */
    private IExceptionResponseFactory $exceptionResponseFactory;
    /** @var IHttpRequestMessage|MockObject */
    private IHttpRequestMessage $request;

    protected function setUp(): void
    {
        $this->exceptionResponseFactory = $this->createMock(IExceptionResponseFactory::class);
        $this->exceptionLogger = $this->createMock(IExceptionLogger::class);
        $this->middleware = new ExceptionHandler($this->exceptionResponseFactory, $this->exceptionLogger);
        $this->request = $this->createMock(IHttpRequestMessage::class);
    }

    public function testHandlingRequestThatDoesNotResultInAnExceptionReturnsResponseFromNextHandler(): void
    {
        /** @var IHttpResponseMessage|MockObject $expectedResponse */
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        /** @var IRequestHandler|MockObject $next */
        $next = $this->createMock(IRequestHandler::class);
        $next->expects($this->once())
            ->method('handle')
            ->with($this->request)
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->middleware->handle($this->request, $next));
    }

    public function testHandlingRequestThatResultsInExceptionLogsTheExceptionAndCreatesAResponseWithIt(): void
    {
        /** @var IHttpResponseMessage|MockObject $expectedResponse */
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $expectedException = new Exception('foo');
        /** @var IRequestHandler|MockObject $next */
        $next = $this->createMock(IRequestHandler::class);
        $next->expects($this->once())
            ->method('handle')
            ->with($this->request)
            ->willThrowException($expectedException);
        $this->exceptionLogger->expects($this->once())
            ->method('logException')
            ->with($expectedException);
        $this->exceptionResponseFactory->expects($this->once())
            ->method('createResponseFromException')
            ->with($expectedException, $this->request)
            ->willReturn($expectedResponse);
        $this->assertSame($expectedResponse, $this->middleware->handle($this->request, $next));
    }
}
