<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Exceptions;

use ErrorException;
use InvalidArgumentException;
use Opulence\Api\Exceptions\ExceptionHandler;
use Opulence\Api\Exceptions\IExceptionResponseFactory;
use Opulence\Api\RequestContext;
use Opulence\Net\Http\Formatting\ResponseWriter;
use Opulence\Net\Http\IHttpResponseMessage;
use Psr\Log\LoggerInterface;

/**
 * Tests the exception handler
 */
class ExceptionHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject The mocked logger */
    private $logger;
    /** @var IExceptionResponseFactory|\PHPUnit_Framework_MockObject_MockObject The exception response factory */
    private $exceptionResponseFactory;
    /** @var ResponseWriter|\PHPUnit_Framework_MockObject_MockObject The response writer */
    private $responseWriter;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->exceptionResponseFactory = $this->createMock(IExceptionResponseFactory::class);
        $this->responseWriter = $this->createMock(ResponseWriter::class);
    }

    public function tearDown(): void
    {
        restore_exception_handler();
    }

    public function testHandlingErrorThatShouldBeLoggedIsLogged(): void
    {
        // Purposely set the thrown level higher than the handled error level so we can just test logging
        $handler = $this->createExceptionHandler(E_NOTICE, E_ERROR);
        $expectedContext = ['foo' => 'bar'];
        $this->logger->expects($this->once())
            ->method('log')
            ->with(E_NOTICE, 'foo', $expectedContext);
        $handler->handleError(E_NOTICE, 'foo', '', 0, $expectedContext);
    }

    public function testHandlingErrorThatShouldBeThrownIsThrown(): void
    {
        try {
            $handler = $this->createExceptionHandler(E_NOTICE, E_ERROR);
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
        $handler = $this->createExceptionHandler(E_ERROR, E_ERROR);
        $this->logger->expects($this->never())
            ->method('log');
        // Handle an error level that's too low to be logged
        $handler->handleError(E_NOTICE, 'foo');
    }

    public function testHandlingErrorThatNotShouldBeThrownIsNotThrown(): void
    {
        $handler = $this->createExceptionHandler(E_NOTICE, E_ERROR);
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

    public function testHandlingExceptionThatShouldNotBeLoggedIsNotLogged(): void
    {
        $handler = $this->createExceptionHandler(null, null, [InvalidArgumentException::class]);
        $this->logger->expects($this->never())
            ->method('error');
        $handler->handleException(new InvalidArgumentException);
    }

    public function testHandlingExceptionCreatesResponseFromResponseFactoryWithRequestContext(): void
    {
        $handler = $this->createExceptionHandler();
        $expectedResponse = $this->createMock(IHttpResponseMessage::class);
        $expectedException = new InvalidArgumentException();
        /** @var RequestContext $expectedRequestContext */
        $expectedRequestContext = $this->createMock(RequestContext::class);
        $this->exceptionResponseFactory->expects($this->once())
            ->method('createResponseFromException')
            ->with($expectedException, $expectedRequestContext)
            ->willReturn($expectedResponse);
        $handler->setRequestContext($expectedRequestContext);
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
     * @param int|null $loggedLevels The bitwise value of error levels that are to be logged
     * @param int|null $thrownLevels The bitwise value of error levels that are to be thrown as exceptions
     * @param array $exceptionsNotLogged The exception or list of exceptions to not log when thrown
     * @return ExceptionHandler The exception handler
     */
    private function createExceptionHandler(
        int $loggedLevels = null,
        int $thrownLevels = null,
        array $exceptionsNotLogged = []
    ): ExceptionHandler {
        $exceptionHandler = new ExceptionHandler(
            $this->logger,
            $this->exceptionResponseFactory,
            $this->responseWriter,
            $loggedLevels,
            $thrownLevels,
            $exceptionsNotLogged
        );
        $exceptionHandler->register();

        return $exceptionHandler;
    }
}
