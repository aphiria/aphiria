<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\StreamBody;

/**
 * Tests the stream body
 */
class StreamBodyTest extends \PHPUnit\Framework\TestCase
{
    public function testCastingToStringConvertsUnderlyingStreamToString(): void
    {
        /** @var IStream $stream */
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn('foo');
        $body = new StreamBody($stream);
        $this->assertEquals('foo', (string)$body);
    }

    public function testReadingAsStreamReturnsUnderlyingStream(): void
    {
        /** @var IStream $stream */
        $stream = $this->createMock(IStream::class);
        $body = new StreamBody($stream);
        $this->assertSame($stream, $body->readAsStream());
    }

    /**
     * Tests reading as a string converts the underlying stream to a string
     */
    public function testReadingAsStringConvertsUnderlyingStreamToString(): void
    {
        /** @var IStream|\PHPUnit_Framework_MockObject_MockObject $stream */
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn('foo');
        $body = new StreamBody($stream);
        $this->assertEquals('foo', $body->readAsString());
    }

    /**
     * Tests writing to a stream writes to an underlying stream
     */
    public function testWritingToStreamForNonSeekableStreamDoesNotRewindItBeforeCopyingIt(): void
    {
        /** @var IStream $outputStream */
        $outputStream = $this->createMock(IStream::class);
        /** @var IStream|\PHPUnit_Framework_MockObject_MockObject $underlyingStream */
        $underlyingStream = $this->createMock(IStream::class);
        $underlyingStream->expects($this->once())
            ->method('copyToStream')
            ->with($outputStream);
        $underlyingStream->expects($this->once())
            ->method('isSeekable')
            ->willReturn(false);
        $underlyingStream->expects($this->never())
            ->method('rewind');
        $body = new StreamBody($underlyingStream);
        $body->writeToStream($outputStream);
    }

    /**
     * Tests writing to a stream writes to an underlying stream
     */
    public function testWritingToStreamForSeekableStreamRewindsItBeforeCopyingIt(): void
    {
        /** @var IStream $outputStream */
        $outputStream = $this->createMock(IStream::class);
        /** @var IStream|\PHPUnit_Framework_MockObject_MockObject $underlyingStream */
        $underlyingStream = $this->createMock(IStream::class);
        $underlyingStream->expects($this->once())
            ->method('copyToStream')
            ->with($outputStream);
        $underlyingStream->expects($this->once())
            ->method('isSeekable')
            ->willReturn(true);
        $underlyingStream->expects($this->once())
            ->method('rewind');
        $body = new StreamBody($underlyingStream);
        $body->writeToStream($outputStream);
    }

    /**
     * Tests writing to a stream writes to an underlying stream
     */
    public function testWritingToStreamWritesToUnderlyingStream(): void
    {
        /** @var IStream $outputStream */
        $outputStream = $this->createMock(IStream::class);
        /** @var IStream|\PHPUnit_Framework_MockObject_MockObject $underlyingStream */
        $underlyingStream = $this->createMock(IStream::class);
        $underlyingStream->expects($this->once())
            ->method('copyToStream')
            ->with($outputStream);
        $body = new StreamBody($underlyingStream);
        $body->writeToStream($outputStream);
    }
}
