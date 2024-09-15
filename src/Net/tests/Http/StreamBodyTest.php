<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\IO\Streams\IStream;
use Aphiria\Net\Http\StreamBody;
use PHPUnit\Framework\TestCase;

class StreamBodyTest extends TestCase
{
    public function testCastingToStringConvertsUnderlyingStreamToString(): void
    {
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn('foo');
        $body = new StreamBody($stream);
        $this->assertSame('foo', (string)$body);
    }

    public function testGettingLengthReturnsUnderlyingStreamLength(): void
    {
        $nullLengthStream = $this->createMock(IStream::class);
        $nullLengthStream->method('$length::get')
            ->willReturn(null);
        $nullLengthBody = new StreamBody($nullLengthStream);
        $this->assertNull($nullLengthBody->length);
        $definedLengthStream = $this->createMock(IStream::class);
        $definedLengthStream->method('$length::1')
            ->willReturn(1);
        $definedLengthBody = new StreamBody($definedLengthStream);
        $this->assertSame(1, $definedLengthBody->length);
    }

    public function testReadingAsStreamReturnsUnderlyingStream(): void
    {
        $stream = $this->createMock(IStream::class);
        $body = new StreamBody($stream);
        $this->assertSame($stream, $body->readAsStream());
    }

    /**
     * Tests reading as a string converts the underlying stream to a string
     */
    public function testReadingAsStringConvertsUnderlyingStreamToString(): void
    {
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn('foo');
        $body = new StreamBody($stream);
        $this->assertSame('foo', $body->readAsString());
    }

    /**
     * Tests writing to a stream writes to an underlying stream
     */
    public function testWritingToStreamForNonSeekableStreamDoesNotRewindItBeforeCopyingIt(): void
    {
        $outputStream = $this->createMock(IStream::class);
        $underlyingStream = $this->createMock(IStream::class);
        $underlyingStream->expects($this->once())
            ->method('copyToStream')
            ->with($outputStream);
        $underlyingStream->isSeekable = false;
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
        $outputStream = $this->createMock(IStream::class);
        $underlyingStream = $this->createMock(IStream::class);
        $underlyingStream->expects($this->once())
            ->method('copyToStream')
            ->with($outputStream);
        $underlyingStream->isSeekable = true;
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
        $outputStream = $this->createMock(IStream::class);
        $underlyingStream = $this->createMock(IStream::class);
        $underlyingStream->expects($this->once())
            ->method('copyToStream')
            ->with($outputStream);
        $body = new StreamBody($underlyingStream);
        $body->writeToStream($outputStream);
    }
}
