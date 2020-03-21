<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Tests;

use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\IExceptionHandler;
use Aphiria\Exceptions\LogLevelRegistry;
use ErrorException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Tests the global exception handler
 */
class GlobalExceptionHandlerTest extends TestCase
{
    /** @var IExceptionHandler|MockObject */
    private IExceptionHandler $exceptionHandler;
    /** @var LoggerInterface|MockObject */
    private LoggerInterface $logger;
    private LogLevelRegistry $logLevels;
    private GlobalExceptionHandler $globalExceptionHandler;
    private int $prevErrorReporting;

    protected function setUp(): void
    {
        $this->prevErrorReporting = \error_reporting();
        $this->exceptionHandler = $this->createMock(IExceptionHandler::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->logLevels = new LogLevelRegistry();
        $this->globalExceptionHandler = new GlobalExceptionHandler(
            $this->exceptionHandler,
            $this->logger,
            $this->logLevels
        );
        $this->globalExceptionHandler->registerWithPhp();
    }

    protected function tearDown(): void
    {
        \error_reporting($this->prevErrorReporting);
        \restore_exception_handler();
    }


    public function testHandlingErrorThatShouldBeThrownIsThrown(): void
    {
        try {
            $this->globalExceptionHandler->handleError(E_ERROR, 'foo');
            $this->fail('Expected error to be thrown as exception');
        } catch (ErrorException $ex) {
            $this->assertEquals(E_ERROR, $ex->getSeverity());
            $this->assertEquals('foo', $ex->getMessage());
        }
    }

    public function testHandlingErrorThatNotShouldBeThrownIsNotThrown(): void
    {
        \error_reporting(\E_ERROR);
        $this->globalExceptionHandler->handleError(E_NOTICE, 'foo');
        // Just by getting here, we've verified that the error was not thrown as an exception
        $this->assertTrue(true);
    }

    public function testHandlingExceptionDefaultsToErrorLogLevelIfExceptionHasNoCustomLogLevel(): void
    {
        $exception = new Exception;
        $this->logger->expects($this->once())
            ->method('error')
            ->with($exception);
        $this->globalExceptionHandler->handleException($exception);
    }

    public function testHandlingExceptionWithCustomErrorLogLevelUsesIt(): void
    {
        $exception = new Exception;
        $this->logger->expects($this->once())
            ->method('emergency')
            ->with($exception);
        $this->logLevels->registerLogLevelFactory(Exception::class, fn (Exception $ex) => LogLevel::EMERGENCY);
        $this->globalExceptionHandler->handleException($exception);
    }
}
