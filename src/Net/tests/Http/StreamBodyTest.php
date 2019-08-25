<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\Net\Http\StreamBody;
use Aphiria\IO\Streams\IStream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the stream body
 */
class StreamBodyTest extends TestCase
{
    public function testCastingToStringConvertsUnderlyingStreamToString(): void
    {
        /** @var IStream|MockObject $stream */
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn('foo');
        $body = new StreamBody($stream);
        $this->assertEquals('foo', (string)$body);
    }

    public function testGettingLengthReturnsUnderlyingStreamLength(): void
    {
        $nullLengthStream = $this->createMock(IStream::class);
        $nullLengthStream->expects($this->once())
            ->method('getLength')
            ->willReturn(null);
        $nullLengthBody = new StreamBody($nullLengthStream);
        $this->assertNull($nullLengthBody->getLength());
        $definedLengthStream = $this->createMock(IStream::class);
        $definedLengthStream->expects($this->once())
            ->method('getLength')
            ->willReturn(1);
        $definedLengthBody = new StreamBody($definedLengthStream);
        $this->assertEquals(1, $definedLengthBody->getLength());
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
        /** @var IStream|MockObject $stream */
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
        /** @var IStream|MockObject $underlyingStream */
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
        /** @var IStream|MockObject $underlyingStream */
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
        /** @var IStream|MockObject $underlyingStream */
        $underlyingStream = $this->createMock(IStream::class);
        $underlyingStream->expects($this->once())
            ->method('copyToStream')
            ->with($outputStream);
        $body = new StreamBody($underlyingStream);
        $body->writeToStream($outputStream);
    }
}
