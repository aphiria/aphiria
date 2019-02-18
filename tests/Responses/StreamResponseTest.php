<?php

/*
 * Opulence
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/console/blob/master/LICENSE.md
 */

namespace Aphiria\Console\Tests\Responses;

use Aphiria\Console\Responses\Compilers\Lexers\ResponseLexer;
use Aphiria\Console\Responses\Compilers\Parsers\ResponseParser;
use Aphiria\Console\Responses\Compilers\ResponseCompiler;
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
    /** @var ResponseCompiler The compiler to use in tests */
    private $compiler;

    public function setUp(): void
    {
        $this->compiler = new ResponseCompiler(new ResponseLexer(), new ResponseParser());
        $this->response = new StreamResponse(fopen('php://memory', 'wb'), $this->compiler);
    }

    public function testGettingStream(): void
    {
        $this->assertTrue(is_resource($this->response->getStream()));
    }

    public function testInvalidStream(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new StreamResponse('foo', $this->compiler);
    }


    public function testWriteOnArray(): void
    {
        $this->response->write(['foo', 'bar']);
        rewind($this->response->getStream());
        $this->assertEquals('foobar', stream_get_contents($this->response->getStream()));
    }

    public function testWriteOnString(): void
    {
        $this->response->write('foo');
        rewind($this->response->getStream());
        $this->assertEquals('foo', stream_get_contents($this->response->getStream()));
    }

    public function testWritelnOnArray(): void
    {
        $this->response->writeln(['foo', 'bar']);
        rewind($this->response->getStream());
        $this->assertEquals('foo' . PHP_EOL . 'bar' . PHP_EOL, stream_get_contents($this->response->getStream()));
    }

    public function testWritelnOnString(): void
    {
        $this->response->writeln('foo');
        rewind($this->response->getStream());
        $this->assertEquals('foo' . PHP_EOL, stream_get_contents($this->response->getStream()));
    }
}
