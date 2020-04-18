<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Exceptions;

use Aphiria\Console\Output\IOutput;
use Aphiria\Framework\Console\Exceptions\ConsoleExceptionRenderer;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

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

    public function testRenderingExceptionWithManyRegisteredOutputWritersWritesMessagesAndReturnsStatusCodes(): void
    {
        $this->exceptionRenderer->registerManyOutputWriters([
            Exception::class => function (Exception $ex, IOutput $output) {
                $output->writeln('foo');

                return 0;
            },
            InvalidArgumentException::class => function (InvalidArgumentException $ex, IOutput $output) {
                $output->writeln('bar');

                return 1;
            }
        ]);
        $this->output->expects($this->at(0))
            ->method('writeln')
            ->with('foo');
        $this->output->expects($this->at(1))
            ->method('writeln')
            ->with('bar');
        $this->exceptionRenderer->render(new Exception());
        $this->exceptionRenderer->render(new InvalidArgumentException());
    }

    public function testRenderingExceptionWithNoRegisteredOutputWriterUsesDefaultResultMessage(): void
    {
        $exception = new Exception();
        $this->output->expects($this->once())
            ->method('writeln')
            ->with(["<fatal>{$exception->getMessage()}" . \PHP_EOL . "{$exception->getTraceAsString()}</fatal>"]);
        $this->exceptionRenderer->render($exception);
    }

    public function testRenderingExceptionWithRegisteredOutputWriterUsesIt(): void
    {
        $this->exceptionRenderer->registerOutputWriter(
            Exception::class,
            function (Exception $ex, IOutput $output) {
                $output->writeln('foo');

                return 1;
            }
        );
        $this->output->expects($this->once())
            ->method('writeln')
            ->with('foo');
        $this->exceptionRenderer->render(new Exception());
    }

    public function testSettingOutputUsesNewOutputToWriteExceptionMessages(): void
    {
        $newOutput = $this->createMock(IOutput::class);
        $newOutput->expects($this->once())
            ->method('writeln')
            ->with('foo');
        $this->exceptionRenderer->setOutput($newOutput);
        $this->exceptionRenderer->registerOutputWriter(
            Exception::class,
            function (Exception $ex, IOutput $output) {
                $output->writeln('foo');

                return 0;
            }
        );
        $this->exceptionRenderer->render(new Exception());
    }
}
