<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/api/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Exceptions;

use Aphiria\Api\Exceptions\ExceptionHandler;
use Aphiria\Api\Exceptions\ExceptionLogLevelFactoryRegistry;
use Aphiria\Api\Exceptions\IExceptionResponseFactory;
use Aphiria\Net\Http\IHttpRequestMessage;
use Aphiria\Net\Http\IHttpResponseMessage;
use Aphiria\Net\Http\IResponseWriter;
use Closure;
use Error;
use ErrorException;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * Tests the exception handler
 */
class ExceptionHandlerTest extends TestCase
{
    /** @var LoggerInterface|MockObject The mocked logger */
    private $logger;
    /** @var IExceptionResponseFactory|MockObject The exception response factory */
    private $exceptionResponseFactory;
    /** @var IResponseWriter|MockObject The response writer */
    private $responseWriter;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->exceptionResponseFactory = $this->createMock(IExceptionResponseFactory::class);
        $this->responseWriter = $this->createMock(IResponseWriter::class);
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
    }

    public function getLogLevels(): array
    {
        return [
            [LogLevel::EMERGENCY],
            [LogLevel::ALERT],
            [LogLevel::CRITICAL],
            [LogLevel::ERROR],
            [LogLevel::WARNING],
            [LogLevel::NOTICE],
            [LogLevel::INFO],
            [LogLevel::DEBUG]
        ];
    }

    public function testHandlingErrorThatShouldBeLoggedIsLogged(): void
    {
        // Purposely set the thrown level higher than the handled error level so we can just test logging
        $handler = $this->createExceptionHandler([], null, E_NOTICE, E_ERROR);
        $expectedContext = ['foo' => 'bar'];
        $this->logger->expects($this->once())
            ->method('log')
            ->with(E_NOTICE, 'foo', $expectedContext);
        $handler->handleError(E_NOTICE, 'foo', '', 0, $expectedContext);
    }

    public function testHandlingErrorThatShouldBeThrownIsThrown(): void
    {
        try {
            $handler = $this->createExceptionHandler([], null, E_NOTICE, E_ERROR);
            $handler->handleError(E_ERROR, 'foo');
            $this->fail('Expected error to be thrown as exception');
        } catch (ErrorException $ex) {
            $this->assertEquals(E_ERROR, $ex->getSeverity());
            $this->assertEquals('foo', $ex->getMessage());
        }
    }

    public function testHandlingErrorThatShouldNotBeLoggedIsNotLogged(): void
    {
        // Purposely set the thrown level higher than the handled error level so we can just test logging
        $handler = $this->createExceptionHandler([], null, E_ERROR, E_ERROR);
        $this->logger->expects($this->never())
            ->method('log');
        // Handle an error level that's too low to be logged
        $handler->handleError(E_NOTICE, 'foo');
    }

    public function testHandlingErrorThatNotShouldBeThrownIsNotThrown(): void
    {
        $handler = $this->createExceptionHandler([], null, E_NOTICE, E_ERROR);
        $handler->handleError(E_NOTICE, 'foo');
        // Just by getting here, we've verified that the error was not thrown as an exception
        $this->assertTrue(true);
    }

    public function testHandlingExceptionThatShouldBeLoggedIsLogged(): void
    {
        $handler = $this->createExceptionHandler();
        $expectedException = new InvalidArgumentException;
        $this->logger->expects($this->once())
            ->method('error')
            ->with($expectedException);
        $handler->handleException($expectedException);
    }

    public function testHandlingExceptionWithErrorThatNotShouldBeThrownIsNotThrown(): void
    {
        $handler = $this->createExceptionHandler();
        $error = new Error;
        $handler->handleException($error);
        $this->assertTrue(true);
    }

    public function testHandlingExceptionWithCustomLevelOnlyLogsItIfErrorLevelIncludesIt(): void
    {
        $exception = new InvalidArgumentException();
        $emergencyHandler = $this->createExceptionHandler(
            [
                InvalidArgumentException::class => function (InvalidArgumentException $ex) {
                    return LogLevel::EMERGENCY;
                },
                RuntimeException::class => function (RuntimeException $ex) {
                    return LogLevel::ERROR;
                }
            ],
            [LogLevel::EMERGENCY]
        );
        $this->logger->expects($this->at(0))
            ->method('emergency')
            ->with($exception);
        $this->logger->expects($this->never())
            ->method('error');
        $emergencyHandler->handleException($exception);
        $emergencyHandler->handleException(new RuntimeException());
    }

    /**
     * @dataProvider getLogLevels
     * @param string $logLevel The log level to use in the test
     */
    public function testHandlingExceptionThatLogsCustomLevelUsesAppropriateLogMethod(string $logLevel): void
    {
        // NOTE: The log levels happen to correspond to the logger methods, too
        $expectedException = new InvalidArgumentException();
        $this->logger->expects($this->once())
            ->method($logLevel)
            ->with($expectedException);
        $handler = $this->createExceptionHandler(
            [
                InvalidArgumentException::class => function (InvalidArgumentException $ex) use ($logLevel) {
                    return $logLevel;
                }
            ],
            // Include the current log level so that it gets logged
            [$logLevel]
        );
        $handler->handleException($expectedException);
    }

    public function testHandlingExceptionCreatesResponseFromResponseFactoryWithRequest(): void
    {
        $handler = $this->createExceptionHandler();
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $expectedException = new InvalidArgumentException();
        /** @var IHttpRequestMessage|MockObject $expectedRequest */
        $expectedRequest = $this->createMock(IHttpRequestMessage::class);
        $this->exceptionResponseFactory->expects($this->once())
            ->method('createResponseFromException')
            ->with($expectedException, $expectedRequest)
            ->willReturn($expectedResponse);
        $handler->setRequest($expectedRequest);
        $this->responseWriter->expects($this->once())
            ->method('writeResponse')
            ->with($this->callback(function (IHttpResponseMessage $response) use ($expectedResponse) {
                return $response === $expectedResponse;
            }));
        $handler->handleException($expectedException);
    }

    /**
     * Creates an instance of an exception handler with certain properties
     *
     * @param Closure[] $customExceptionsToLogLevels The exception types to closures that return the PSR-3 log levels
     * @param array|null $minExceptionLogLevels The minimum PSR-3 log levels that will be logged
     * @param int $errorLogLevels The bitwise value of error levels that are to be logged
     * @param int $errorThrownLevels The bitwise value of error levels that are to be thrown as exceptions
     * @return ExceptionHandler The exception handler
     */
    private function createExceptionHandler(
        array $customExceptionsToLogLevels = [],
        array $minExceptionLogLevels = null,
        int $errorLogLevels = 0,
        int $errorThrownLevels = E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED)
    ): ExceptionHandler {
        $exceptionLogLevelFactories = new ExceptionLogLevelFactoryRegistry();
        $exceptionLogLevelFactories->registerManyFactories($customExceptionsToLogLevels);
        $exceptionHandler = new ExceptionHandler(
            $this->exceptionResponseFactory,
            $this->logger,
            $exceptionLogLevelFactories,
            $minExceptionLogLevels,
            $errorLogLevels,
            $errorThrownLevels,
            $this->responseWriter
        );
        $exceptionHandler->registerWithPhp();

        return $exceptionHandler;
    }
}
