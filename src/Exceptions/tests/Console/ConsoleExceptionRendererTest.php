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
use Aphiria\Exceptions\Console\ConsoleExceptionRenderer;
use Aphiria\Exceptions\Console\ExceptionResult;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the console exception renderer
 */
class ConsoleExceptionRendererTest extends TestCase
{
    private ConsoleExceptionRenderer $exceptionRenderer;
    /** @var IOutput|MockObject */
    private IOutput $output;

    protected function setUp(): void
    {
        $this->output = $this->createMock(IOutput::class);
        $this->exceptionRenderer = new ConsoleExceptionRenderer($this->output, false);
    }

    public function testRenderingExceptionWithManyRegisteredResultFactoryUsesResultsStatusAndMessages(): void
    {
        $this->exceptionRenderer->registerManyExceptionResultFactories([
            Exception::class => fn (Exception $ex) => new ExceptionResult(0, 'foo'),
            InvalidArgumentException::class => fn (InvalidArgumentException $ex) => new ExceptionResult(1, 'bar')
        ]);
        $this->output->expects($this->at(0))
            ->method('writeln')
            ->with(['foo']);
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with(['bar']);
        $this->exceptionRenderer->render(new Exception);
        $this->exceptionRenderer->render(new InvalidArgumentException);
    }

    public function testRenderingExceptionWithNoRegisteredResultFactoryUsesDefaultResult(): void
    {
        $exception = new Exception();
        $this->output->expects($this->once())
            ->method('writeln')
            ->with(["<fatal>{$exception->getMessage()}" . \PHP_EOL . "{$exception->getTraceAsString()}</fatal>"]);
        $this->exceptionRenderer->render($exception);
    }

    public function testRenderingExceptionWithRegisteredResultFactoryUsesResultsStatusAndMessages(): void
    {
        $this->exceptionRenderer->registerExceptionResultFactory(
            Exception::class,
            fn (Exception $ex) => new ExceptionResult(0, 'foo')
        );
        $this->output->expects($this->once())
            ->method('writeln')
            ->with(['foo']);
        $this->exceptionRenderer->render(new Exception);
    }

    public function testSettingOutputUsesNewOutputToWriteExceptionMessages(): void
    {
        $newOutput = $this->createMock(IOutput::class);
        $newOutput->expects($this->once())
            ->method('writeln')
            ->with(['foo']);
        $this->exceptionRenderer->setOutput($newOutput);
        $this->exceptionRenderer->registerExceptionResultFactory(
            Exception::class,
            fn (Exception $ex) => new ExceptionResult(0, 'foo')
        );
        $this->exceptionRenderer->render(new Exception);
    }
}
