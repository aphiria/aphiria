<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Console\Tests\Output;

use Aphiria\Console\Output\Compilers\OutputCompiler;
use Aphiria\Console\Output\StreamOutput;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Tests the stream output
 */
class StreamOutputTest extends TestCase
{
    /** @var StreamOutput */
    private $output;
    /** @var resource */
    private $inputStream;
    /** @var OutputCompiler */
    private $compiler;

    protected function setUp(): void
    {
        $this->inputStream = fopen('php://memory', 'wb');
        $this->compiler = new OutputCompiler();
        $this->output = new StreamOutput(fopen('php://memory', 'wb'), $this->inputStream, $this->compiler);
    }

    public function testGettingStream(): void
    {
        $this->assertIsResource($this->output->getOutputStream());
    }

    public function testInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StreamOutput('foo', $this->compiler);
    }

    public function testReadingLineReadsFromInputStream(): void
    {
        fwrite($this->inputStream, 'foo');
        rewind($this->inputStream);
        $this->assertEquals('foo', $this->output->readLine());
    }

    public function testReadingLineWhenNotAtEndOfFileThrowsRuntimeException(): void
    {
        $filePath = sys_get_temp_dir() . '/output.txt';
        file_put_contents($filePath, 'fake_data');

        $inputStream = fopen($filePath, 'r');
        $outputStream = fopen($filePath, 'r');
        $output = new StreamOutput($outputStream, $inputStream, $this->compiler);
        try {
            $output->readLine();
        } catch (RuntimeException $e) {
            $this->assertSame('Failed to read line', $e->getMessage());
        } finally {
            fclose($inputStream);
            fclose($outputStream);
        }
    }

    public function testWriteOnArray(): void
    {
        $this->output->write(['foo', 'bar']);
        rewind($this->output->getOutputStream());
        $this->assertEquals('foobar', stream_get_contents($this->output->getOutputStream()));
    }

    public function testWriteOnString(): void
    {
        $this->output->write('foo');
        rewind($this->output->getOutputStream());
        $this->assertEquals('foo', stream_get_contents($this->output->getOutputStream()));
    }

    public function testWritelnOnArray(): void
    {
        $this->output->writeln(['foo', 'bar']);
        rewind($this->output->getOutputStream());
        $this->assertEquals('foo' . PHP_EOL . 'bar' . PHP_EOL, stream_get_contents($this->output->getOutputStream()));
    }

    public function testWritelnOnString(): void
    {
        $this->output->writeln('foo');
        rewind($this->output->getOutputStream());
        $this->assertEquals('foo' . PHP_EOL, stream_get_contents($this->output->getOutputStream()));
    }
}
