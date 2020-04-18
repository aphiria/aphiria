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

use Aphiria\Exceptions\FatalErrorException;
use Aphiria\Exceptions\GlobalExceptionHandler;
use Aphiria\Exceptions\IExceptionRenderer;
use ErrorException;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Tests the global exception handler
 */
class GlobalExceptionHandlerTest extends TestCase
{
    /** @var IExceptionRenderer|MockObject */
    private IExceptionRenderer $exceptionRenderer;
    /** @var LoggerInterface|MockObject */
    private LoggerInterface $logger;
    private GlobalExceptionHandler $globalExceptionHandler;
    private int $prevErrorReporting;

    protected function setUp(): void
    {
        $this->prevErrorReporting = \error_reporting();
        $this->exceptionRenderer = $this->createMock(IExceptionRenderer::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->globalExceptionHandler = new GlobalExceptionHandler($this->exceptionRenderer, $this->logger);
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
        $exception = new Exception();
        $this->logger->expects($this->once())
            ->method('error')
            ->with($exception);
        $this->globalExceptionHandler->handleException($exception);
    }

    public function testHandlingExceptionRendersException(): void
    {
        $exception = new Exception();
        $this->exceptionRenderer->expects($this->once())
            ->method('render')
            ->with($exception);
        $this->globalExceptionHandler->handleException($exception);
    }

    public function testHandlingExceptionWithManyCustomErrorLogLevelUsesThem(): void
    {
        $exception = new Exception();
        $this->logger->expects($this->once())
            ->method('emergency')
            ->with($exception);
        $this->globalExceptionHandler->registerManyLogLevelFactories([
            Exception::class => fn (Exception $ex) => LogLevel::EMERGENCY
        ]);
        $this->globalExceptionHandler->handleException($exception);
    }

    public function testHandlingExceptionWithSingleCustomErrorLogLevelUsesIt(): void
    {
        $exception = new Exception();
        $this->logger->expects($this->once())
            ->method('emergency')
            ->with($exception);
        $this->globalExceptionHandler->registerLogLevelFactory(Exception::class, fn (Exception $ex) => LogLevel::EMERGENCY);
        $this->globalExceptionHandler->handleException($exception);
    }

    public function testHandleShutdownThrowsErrorsAsExceptions(): void
    {
        $errors = [
            ['type' => \E_ERROR, 'message' => 'foo', 'file' => '/file', 'line' => 1],
            ['type' => \E_PARSE, 'message' => 'foo', 'file' => '/file', 'line' => 1],
            ['type' => \E_CORE_ERROR, 'message' => 'foo', 'file' => '/file', 'line' => 1],
            ['type' => \E_COMPILE_ERROR, 'message' => 'foo', 'file' => '/file', 'line' => 1]
        ];
        $globalExceptionHandler = new class($this->createMock(IExceptionRenderer::class)) extends GlobalExceptionHandler {
            public ?Throwable $handledException = null;

            public function handleException(Throwable $ex): void
            {
                $this->handledException = $ex;
            }
        };

        foreach ($errors as $error) {
            $globalExceptionHandler->handleShutdown($error);
            $this->assertInstanceOf(FatalErrorException::class, $globalExceptionHandler->handledException);
            $this->assertEquals($error['message'], $globalExceptionHandler->handledException->getMessage());
            $this->assertEquals($error['type'], $globalExceptionHandler->handledException->getCode());
            $this->assertEquals(0, $globalExceptionHandler->handledException->getSeverity());
            $this->assertEquals($error['file'], $globalExceptionHandler->handledException->getFile());
            $this->assertEquals($error['line'], $globalExceptionHandler->handledException->getLine());
        }
    }
}
