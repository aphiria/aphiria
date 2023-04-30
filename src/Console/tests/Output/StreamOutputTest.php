<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output;

use Aphiria\Console\Output\Compilers\OutputCompiler;
use Aphiria\Console\Output\StreamOutput;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StreamOutputTest extends TestCase
{
    private OutputCompiler $compiler;
    /** @var resource|bool */
    private mixed $inputStream;
    private StreamOutput $output;

    protected function setUp(): void
    {
        $this->inputStream = \fopen('php://memory', 'wb');
        $this->compiler = new OutputCompiler();
        $this->output = new StreamOutput(\fopen('php://memory', 'wb'), $this->inputStream, $this->compiler);
    }

    public function testClearDoesNothing(): void
    {
        $this->output->clear();
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testGettingStream(): void
    {
        $this->assertIsResource($this->output->outputStream);
    }

    public function testInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress InvalidArgument We're purposely testing passing in the wrong type */
        new StreamOutput('foo', $this->compiler);
    }

    public function testReadingLineReadsFromInputStream(): void
    {
        \fwrite($this->inputStream, 'foo');
        \rewind($this->inputStream);
        $this->assertSame('foo', $this->output->readLine());
    }

    public function testReadingLineThatFailsThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to read line');
        $this->inputStream = false;
        $this->output->readLine();
    }

    public function testWritelnOnArray(): void
    {
        $this->output->writeln(['foo', 'bar']);
        \rewind($this->output->outputStream);
        $this->assertSame('foo' . PHP_EOL . 'bar' . PHP_EOL, \stream_get_contents($this->output->outputStream));
    }

    public function testWritelnOnString(): void
    {
        $this->output->writeln('foo');
        \rewind($this->output->outputStream);
        $this->assertSame('foo' . PHP_EOL, \stream_get_contents($this->output->outputStream));
    }

    public function testWriteOnArray(): void
    {
        $this->output->write(['foo', 'bar']);
        \rewind($this->output->outputStream);
        $this->assertSame('foobar', \stream_get_contents($this->output->outputStream));
    }

    public function testWriteOnString(): void
    {
        $this->output->write('foo');
        \rewind($this->output->outputStream);
        $this->assertSame('foo', \stream_get_contents($this->output->outputStream));
    }
}
