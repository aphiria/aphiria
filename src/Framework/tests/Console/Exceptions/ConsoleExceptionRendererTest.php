<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Framework\Tests\Console\Exceptions;

use Aphiria\Console\Output\IOutput;
use Aphiria\Framework\Console\Exceptions\ConsoleExceptionRenderer;
use Exception;
use InvalidArgumentException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class ConsoleExceptionRendererTest extends TestCase
{
    private ConsoleExceptionRenderer $exceptionRenderer;
    private IOutput&MockInterface $output;

    protected function setUp(): void
    {
        $this->output = Mockery::mock(IOutput::class);
        $this->exceptionRenderer = new ConsoleExceptionRenderer($this->output, false);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testRenderingExceptionWithManyRegisteredOutputWritersWritesMessagesAndReturnsStatusCodes(): void
    {
        $this->exceptionRenderer->registerManyOutputWriters([
            Exception::class => function (Exception $ex, IOutput $output): int {
                $output->writeln('foo');

                return 0;
            },
            InvalidArgumentException::class => function (InvalidArgumentException $ex, IOutput $output): int {
                $output->writeln('bar');

                return 1;
            }
        ]);
        $this->output->shouldReceive('writeln')
            ->with('foo');
        $this->output->shouldReceive('writeln')
            ->with('bar');
        $this->exceptionRenderer->render(new Exception());
        $this->exceptionRenderer->render(new InvalidArgumentException());
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testRenderingExceptionWithNoRegisteredOutputWriterUsesDefaultResultMessage(): void
    {
        $exception = new Exception();
        $this->output->shouldReceive('writeln')
            ->once()
            ->with(["<fatal>{$exception->getMessage()}" . \PHP_EOL . "{$exception->getTraceAsString()}</fatal>"]);
        $this->exceptionRenderer->render($exception);
        // Dummy assertion
        $this->assertTrue(true);
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
        $this->output->shouldReceive('writeln')
            ->once()
            ->with('foo');
        $this->exceptionRenderer->render(new Exception());
        // Dummy assertion
        $this->assertTrue(true);
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
