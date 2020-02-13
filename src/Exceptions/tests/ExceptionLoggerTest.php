<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Api\Tests\Exceptions;

use Aphiria\Exceptions\ExceptionLogger;
use Aphiria\Exceptions\ExceptionLogLevelFactoryRegistry;
use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;

/**
 * Tests the exception logger
 */
class ExceptionLoggerTest extends TestCase
{
    /** @var LoggerInterface|MockObject */
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
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
        $exceptionLogger = $this->createExceptionLogger([], null, E_NOTICE);
        $expectedContext = ['foo' => 'bar'];
        $this->logger->expects($this->once())
            ->method('log')
            ->with(E_NOTICE, 'foo', $expectedContext);
        $exceptionLogger->logError(E_NOTICE, 'foo', '', 0, $expectedContext);
    }

    public function testHandlingErrorThatShouldNotBeLoggedIsNotLogged(): void
    {
        // Purposely set the thrown level higher than the handled error level so we can just test logging
        $exceptionLogger = $this->createExceptionLogger([], null, E_ERROR);
        $this->logger->expects($this->never())
            ->method('log');
        // Handle an error level that's too low to be logged
        $exceptionLogger->logError(E_NOTICE, 'foo');
    }

    public function testHandlingExceptionThatShouldBeLoggedIsLogged(): void
    {
        $exceptionLogger = $this->createExceptionLogger();
        $expectedException = new InvalidArgumentException();
        $this->logger->expects($this->once())
            ->method('error')
            ->with($expectedException);
        $exceptionLogger->logException($expectedException);
    }

    public function testHandlingExceptionWithCustomLevelOnlyLogsItIfErrorLevelIncludesIt(): void
    {
        $exception = new InvalidArgumentException();
        $exceptionLogger = $this->createExceptionLogger(
            [
                InvalidArgumentException::class => fn (InvalidArgumentException $ex) => LogLevel::EMERGENCY,
                RuntimeException::class => fn (RuntimeException $ex) => LogLevel::ERROR
            ],
            [LogLevel::EMERGENCY]
        );
        $this->logger->expects($this->at(0))
            ->method('emergency')
            ->with($exception);
        $this->logger->expects($this->never())
            ->method('error');
        $exceptionLogger->logException($exception);
        $exceptionLogger->logException(new RuntimeException());
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
        $exceptionLogger = $this->createExceptionLogger(
            [
                InvalidArgumentException::class => fn (InvalidArgumentException $ex) => $logLevel
            ],
            // Include the current log level so that it gets logged
            [$logLevel]
        );
        $exceptionLogger->logException($expectedException);
    }

    /**
     * Creates an instance of an exception logger with certain properties
     *
     * @param Closure[] $customExceptionsToLogLevels The exception types to closures that return the PSR-3 log levels
     * @param array|null $minExceptionLogLevels The minimum PSR-3 log levels that will be logged
     * @param int $errorLogLevels The bitwise value of error levels that are to be logged
     * @return ExceptionLogger The exception logger
     */
    private function createExceptionLogger(
        array $customExceptionsToLogLevels = [],
        array $minExceptionLogLevels = null,
        int $errorLogLevels = 0
    ): ExceptionLogger {
        $exceptionLogLevelFactories = new ExceptionLogLevelFactoryRegistry();
        $exceptionLogLevelFactories->registerManyFactories($customExceptionsToLogLevels);

        return new ExceptionLogger($this->logger, $exceptionLogLevelFactories, $minExceptionLogLevels, $errorLogLevels);
    }
}
