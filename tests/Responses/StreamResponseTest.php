<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses;

use Aphiria\Console\Responses\Compilers\Compiler;
use Aphiria\Console\Responses\Compilers\Lexers\Lexer;
use Aphiria\Console\Responses\Compilers\Parsers\Parser;
use Aphiria\Console\Responses\StreamResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the stream response
 */
class StreamResponseTest extends TestCase
{
    /** @var StreamResponse The response to use in tests */
    private $response;
    /** @var Compiler The compiler to use in tests */
    private $compiler;

    /**
     * Sets up the tests
     */
    public function setUp(): void
    {
        $this->compiler = new Compiler(new Lexer(), new Parser());
        $this->response = new StreamResponse(fopen('php://memory', 'wb'), $this->compiler);
    }

    /**
     * Tests getting the stream
     */
    public function testGettingStream(): void
    {
        $this->assertTrue(is_resource($this->response->getStream()));
    }

    /**
     * Tests an invalid stream
     */
    public function testInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StreamResponse('foo', $this->compiler);
    }

    /**
     * Test writing an array message
     */
    public function testWriteOnArray(): void
    {
        $this->response->write(['foo', 'bar']);
        rewind($this->response->getStream());
        $this->assertEquals('foobar', stream_get_contents($this->response->getStream()));
    }

    /**
     * Tests writing a string message
     */
    public function testWriteOnString(): void
    {
        $this->response->write('foo');
        rewind($this->response->getStream());
        $this->assertEquals('foo', stream_get_contents($this->response->getStream()));
    }

    /**
     * Test writing an array message to a line
     */
    public function testWritelnOnArray(): void
    {
        $this->response->writeln(['foo', 'bar']);
        rewind($this->response->getStream());
        $this->assertEquals('foo' . PHP_EOL . 'bar' . PHP_EOL, stream_get_contents($this->response->getStream()));
    }

    /**
     * Tests writing a string message to a line
     */
    public function testWritelnOnString(): void
    {
        $this->response->writeln('foo');
        rewind($this->response->getStream());
        $this->assertEquals('foo' . PHP_EOL, stream_get_contents($this->response->getStream()));
    }
}
