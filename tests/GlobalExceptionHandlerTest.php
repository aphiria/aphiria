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

use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\IExceptionLogger;
use Aphiria\Exceptions\IExceptionResponseFactory;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\IResponseWriter;
use Error;
use ErrorException;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the global exception handler
 */
class GlobalExceptionHandlerTest extends TestCase
{
    /** @var IExceptionLogger|MockObject */
    private IExceptionLogger $exceptionLogger;
    /** @var IExceptionResponseFactory|MockObject */
    private IExceptionResponseFactory $exceptionResponseFactory;
    /** @var IResponseWriter|MockObject */
    private IResponseWriter $responseWriter;

    protected function setUp(): void
    {
        $this->exceptionLogger = $this->createMock(IExceptionLogger::class);
        $this->exceptionResponseFactory = $this->createMock(IExceptionResponseFactory::class);
        $this->responseWriter = $this->createMock(IResponseWriter::class);
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
    }

    public function testHandlingErrorThatShouldBeThrownIsThrown(): void
    {
        try {
            $handler = $this->createExceptionHandler(E_ERROR);
            $handler->handleError(E_ERROR, 'foo');
            $this->fail('Expected error to be thrown as exception');
        } catch (ErrorException $ex) {
            $this->assertEquals(E_ERROR, $ex->getSeverity());
            $this->assertEquals('foo', $ex->getMessage());
        }
    }

    public function testHandlingErrorThatNotShouldBeThrownIsNotThrown(): void
    {
        $handler = $this->createExceptionHandler(E_ERROR);
        $handler->handleError(E_NOTICE, 'foo');
        // Just by getting here, we've verified that the error was not thrown as an exception
        $this->assertTrue(true);
    }

    public function testHandlingExceptionWithErrorThatNotShouldBeThrownIsNotThrown(): void
    {
        $handler = $this->createExceptionHandler();
        $error = new Error;
        $handler->handleException($error);
        $this->assertTrue(true);
    }

    public function testHandlingExceptionCreatesResponseFromResponseFactoryWithNoRequest(): void
    {
        $handler = $this->createExceptionHandler();
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $expectedException = new InvalidArgumentException();
        $this->exceptionResponseFactory->expects($this->once())
            ->method('createResponseFromException')
            ->with($expectedException, null)
            ->willReturn($expectedResponse);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($this->callback(fn (IHttpResponseMessage $response) => $response === $expectedResponse));
        $handler->handleException($expectedException);
    }

    /**
     * Creates an instance of a global exception handler with certain properties
     *
     * @param int $errorThrownLevels The bitwise value of error levels that are to be thrown as exceptions
     * @return GlobalExceptionHandler The exception handler
     */
    private function createExceptionHandler(
        int $errorThrownLevels = E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED)
    ): GlobalExceptionHandler {
        $exceptionHandler = new GlobalExceptionHandler(
            $this->exceptionResponseFactory,
            $this->exceptionLogger,
            $errorThrownLevels,
            $this->responseWriter
        );
        $exceptionHandler->registerWithPhp();

        return $exceptionHandler;
    }
}
