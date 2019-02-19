<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

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
    /** @var StreamOutput */
    private $output;
    /** @var OutputCompiler */
    private $compiler;

    public function setUp(): void
    {
        $this->compiler = new OutputCompiler();
        $this->output = new StreamOutput(fopen('php://memory', 'wb'), $this->compiler);
    }

    public function testGettingStream(): void
    {
        $this->assertTrue(is_resource($this->output->getStream()));
    }

    public function testInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StreamOutput('foo', $this->compiler);
    }


    public function testWriteOnArray(): void
    {
        $this->output->write(['foo', 'bar']);
        rewind($this->output->getStream());
        $this->assertEquals('foobar', stream_get_contents($this->output->getStream()));
    }

    public function testWriteOnString(): void
    {
        $this->output->write('foo');
        rewind($this->output->getStream());
        $this->assertEquals('foo', stream_get_contents($this->output->getStream()));
    }

    public function testWritelnOnArray(): void
    {
        $this->output->writeln(['foo', 'bar']);
        rewind($this->output->getStream());
        $this->assertEquals('foo' . PHP_EOL . 'bar' . PHP_EOL, stream_get_contents($this->output->getStream()));
    }

    public function testWritelnOnString(): void
    {
        $this->output->writeln('foo');
        rewind($this->output->getStream());
        $this->assertEquals('foo' . PHP_EOL, stream_get_contents($this->output->getStream()));
    }
}
