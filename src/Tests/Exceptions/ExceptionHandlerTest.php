<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Api\Tests\Exceptions;

use Opulence\Api\Exceptions\ExceptionHandler;
use Opulence\Api\Exceptions\ExceptionResponseFactoryRegistry;
use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\Formatting\ResponseWriter;
use Psr\Log\LoggerInterface;

/**
 * Tests the exception handler
 */
class ExceptionHandlerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ExceptionHandler The exception handler to use in tests */
    private $handler;
    /** @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject The mocked logger */
    private $logger;
    /** @var ExceptionResponseFactoryRegistry The exception response factory */
    private $exceptionResponseFactories;
    /** @var ResponseWriter The response writer */
    private $responseWriter;
    /** @var IStream|\PHPUnit_Framework_MockObject_MockObject The mocked output stream */
    private $outputStream;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->exceptionResponseFactories = new ExceptionResponseFactoryRegistry();
        $this->outputStream = $this->createMock(IStream::class);
        $this->responseWriter = new ResponseWriter($this->outputStream);
    }

    public function tearDown(): void
    {
        restore_exception_handler();
    }

    public function testFoo(): void
    {
        // Dummy test to stop getting exceptions
        $this->assertTrue(true);
    }

    /**
     * Creates an instance of an exception handler with certain properties
     *
     * @param array $exceptionsNotLogged The exception or list of exceptions to not log when thrown
     * @param int|null $loggedLevels The bitwise value of error levels that are to be logged
     * @param int|null $thrownLevels The bitwise value of error levels that are to be thrown as exceptions
     * @return ExceptionHandler The exception handler
     */
    private function createExceptionHandler(
        array $exceptionsNotLogged = [],
        int $loggedLevels = null,
        int $thrownLevels = null
    ): ExceptionHandler {
        $exceptionHandler = new ExceptionHandler(
            $this->logger,
            $this->exceptionResponseFactories,
            $this->responseWriter,
            $exceptionsNotLogged,
            $loggedLevels,
            $thrownLevels
        );
        $exceptionHandler->register();

        return $exceptionHandler;
    }
}
