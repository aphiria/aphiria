<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\IO\Streams\IStream;

/**
 * Tests the stream body
 */
class StreamBodyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests reading as a stream returns the underlying stream
     */
    public function testReadingAsStreamReturnsUnderlyingStream() : void
    {
        $stream = $this->createMock(IStream::class);
        $body = new StreamBody($stream);
        $this->assertSame($stream, $body->readAsStream());
    }

    /**
     * Tests reading as a string converts the underlying stream to a string
     */
    public function testReadingAsStringConvertsUnderlyingStreamToString() : void
    {
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->returns('foo');
        $body = new StreamBody($stream);
        $this->assertEquals('foo', $body->readAsString());
    }

    /**
     * Tests writing to a stream writes to an underlying stream
     */
    public function testWritingToStreamWritesToUnderlyingStream() : void
    {
        $underlyingStream = $this->createMock(IStream::class);
        $underlyingStream->expects($this->once())
            ->method('__toString')
            ->returns('foo');
        $outputStream = $this->createMock(IStream::class);
        $outputStream->expects($this->once())
            ->method('write')
            ->with('foo');
        $body = new StreamBody($underlyingStream);
        $body->writeToStream($outputStream);
    }
}
