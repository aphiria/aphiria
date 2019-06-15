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

/**
 * Tests the stream output
 */
class StreamOutputTest extends TestCase
{
    private StreamOutput $output;
    /** @var resource */
    private $inputStream;
    private OutputCompiler $compiler;

    protected function setUp(): void
    {
        $this->inputStream = fopen('php://memory', 'wb');
        $this->compiler = new OutputCompiler();
        $this->output = new StreamOutput(fopen('php://memory', 'wb'), $this->inputStream, $this->compiler);
    }

    public function testGettingStream(): void
    {
        $this->assertTrue(is_resource($this->output->getOutputStream()));
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
