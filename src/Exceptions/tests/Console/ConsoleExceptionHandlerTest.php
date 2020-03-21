<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Exceptions\Tests\Console;

use Aphiria\Console\Output\IOutput;
use Aphiria\Exceptions\Console\ConsoleExceptionHandler;
use Aphiria\Exceptions\Console\ExceptionResult;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console exception handler
 */
class ConsoleExceptionHandlerTest extends TestCase
{
    private ConsoleExceptionHandler $exceptionHandler;
    /** @var IOutput|MockObject */
    private IOutput $output;

    protected function setUp(): void
    {
        $this->output = $this->createMock(IOutput::class);
        $this->exceptionHandler = new ConsoleExceptionHandler($this->output, false);
    }

    public function testHandlingExceptionWithManyRegisteredResultFactoryUsesResultsStatusAndMessages(): void
    {
        $this->exceptionHandler->registerManyExceptionResultFactories([
            Exception::class => fn (Exception $ex) => new ExceptionResult(0, 'foo'),
            InvalidArgumentException::class => fn (InvalidArgumentException $ex) => new ExceptionResult(1, 'bar')
        ]);
        $this->output->expects($this->at(0))
            ->method('writeln')
            ->with(['foo']);
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with(['bar']);
        $this->exceptionHandler->handle(new Exception);
        $this->exceptionHandler->handle(new InvalidArgumentException);
    }

    public function testHandlingExceptionWithNoRegisteredResultFactoryUsesDefaultResult(): void
    {
        $exception = new Exception();
        $this->output->expects($this->once())
            ->method('writeln')
            ->with(["<fatal>{$exception->getMessage()}" . \PHP_EOL . "{$exception->getTraceAsString()}</fatal>"]);
        $this->exceptionHandler->handle($exception);
    }

    public function testHandlingExceptionWithRegisteredResultFactoryUsesResultsStatusAndMessages(): void
    {
        $this->exceptionHandler->registerExceptionResultFactory(
            Exception::class,
            fn (Exception $ex) => new ExceptionResult(0, 'foo')
        );
        $this->output->expects($this->once())
            ->method('writeln')
            ->with(['foo']);
        $this->exceptionHandler->handle(new Exception);
    }

    public function testSettingOutputUsesNewOutputToWriteExceptionMessages(): void
    {
        $newOutput = $this->createMock(IOutput::class);
        $newOutput->expects($this->once())
            ->method('writeln')
            ->with(['foo']);
        $this->exceptionHandler->setOutput($newOutput);
        $this->exceptionHandler->registerExceptionResultFactory(
            Exception::class,
            fn (Exception $ex) => new ExceptionResult(0, 'foo')
        );
        $this->exceptionHandler->handle(new Exception);
    }
}
