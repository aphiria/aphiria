<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\IO\Tests\Streams;

use Aphiria\IO\Streams\IStream;
use Aphiria\IO\Streams\MultiStream;
use Aphiria\IO\Streams\Stream;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MultiStreamTest extends TestCase
{
    private MultiStream $multiStream;

    protected function setUp(): void
    {
        $this->multiStream = new MultiStream();
    }

    public function testAddingUnreadableStreamThrowsAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $unreadableStream = $this->createMock(IStream::class);
        $unreadableStream->method(PropertyHook::get('isReadable'))
            ->willReturn(false);
        $this->multiStream->addStream($unreadableStream);
    }

    public function testClosingStreamMakesItSeekableAgainAndResetsThePosition(): void
    {
        $unseekableStream = $this->createReadableStream();
        $unseekableStream->method(PropertyHook::get('isSeekable'))
            ->willReturn(false);
        $this->multiStream->addStream($unseekableStream);
        $this->multiStream->close();
        $this->assertTrue($this->multiStream->isSeekable);
        $this->assertSame(0, $this->multiStream->position);
    }

    public function testClosingStreamUnsetsSubstreamResources(): void
    {
        $handle1 = \fopen('php://temp', 'rb');
        $stream1 = new Stream($handle1);
        $handle2 = \fopen('php://memory', 'rb');
        $stream2 = new Stream($handle2);
        $this->multiStream->addStream($stream1);
        $this->multiStream->addStream($stream2);
        $this->multiStream->close();
        $this->assertFalse(\is_resource($handle1));
        $this->assertFalse(\is_resource($handle2));
    }

    public function testConstructingAddsStreams(): void
    {
        $stream1 = new Stream(\fopen('php://temp', 'r+b'));
        $stream1->write('foo');
        $stream2 = new Stream(\fopen('php://temp', 'r+b'));
        $stream2->write('bar');
        $multistream = new MultiStream([$stream1, $stream2]);
        $this->assertSame('foobar', (string)$multistream);
    }

    public function testCopyingToClosedStreamThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $stream = new Stream(\fopen('php://temp', 'r+b'));
        $stream->write('foo');
        $stream->rewind();
        $this->multiStream->addStream($stream);
        $destinationStream = new Stream(\fopen('php://temp', 'r+b'));
        $destinationStream->close();
        $this->multiStream->copyToStream($destinationStream, 1);
    }

    public function testCopyingToStreamCopiesAllContentsUsingBufferSize(): void
    {
        $stream1 = new Stream(\fopen('php://temp', 'r+b'));
        $stream2 = new Stream(\fopen('php://temp', 'r+b'));
        $stream1->write('foo');
        $stream1->write('bar');
        $stream1->rewind();
        $stream2->rewind();
        $this->multiStream->addStream($stream1);
        $this->multiStream->addStream($stream2);
        $destinationStream = new Stream(\fopen('php://temp', 'r+b'));
        $this->multiStream->copyToStream($destinationStream, 1);
        $destinationStream->rewind();
        $this->assertSame('foobar', $destinationStream->readToEnd());
    }

    public function testDestroyingStreamUnsetsSubstreamResources(): void
    {
        $handle1 = \fopen('php://temp', 'rb');
        $stream1 = new Stream($handle1);
        $handle2 = \fopen('php://memory', 'rb');
        $stream2 = new Stream($handle2);
        $this->multiStream->addStream($stream1);
        $this->multiStream->addStream($stream2);
        unset($this->multiStream);
        $this->assertFalse(\is_resource($handle1));
        $this->assertFalse(\is_resource($handle2));
    }

    public function testEofOnlyReturnsTrueIfLastStreamIsAtEof(): void
    {
        $stream1 = new Stream(\fopen('php://temp', 'r+b'));
        $stream2 = new Stream(\fopen('php://temp', 'r+b'));
        $stream1->write('foo');
        $stream1->rewind();
        $stream2->write('bar');
        $stream2->rewind();
        $this->multiStream->addStream($stream1);
        $this->multiStream->addStream($stream2);
        // Test that it returns false when not on the last stream
        $this->assertFalse($this->multiStream->isEof);
        // Test that it returns false when on the last stream, but that stream isn't at EOF
        $this->multiStream->read(3);
        $this->assertFalse($this->multiStream->isEof);
        // Test that it returns true when on the last stream, and that stream is at EOF
        $this->multiStream->read(3);
        // Read one additional char to get to the EOF
        $this->multiStream->read(1);
        $this->assertTrue($this->multiStream->isEof);
    }

    public function testEofThrowsExceptionWithNoStreams(): void
    {
        $this->expectException(RuntimeException::class);
        $this->multiStream->isEof;
    }

    public function testGettingLengthWillReturnNullIfAnyStreamsHaveNullLength(): void
    {
        $streamWithLength = $this->createReadableStream();
        $streamWithoutLength = $this->createReadableStream();
        $streamWithLength->method(PropertyHook::get('length'))
            ->willReturn(10);
        $streamWithoutLength->method(PropertyHook::get('length'))
            ->willReturn(null);
        $this->multiStream->addStream($streamWithLength);
        $this->multiStream->addStream($streamWithoutLength);
        $this->assertNull($this->multiStream->length);
    }

    public function testGettingLengthWillSumLengthsOfStreams(): void
    {
        $stream1 = $this->createReadableStream();
        $stream2 = $this->createReadableStream();
        $stream1->method(PropertyHook::get('length'))
            ->willReturn(10);
        $stream2->method(PropertyHook::get('length'))
            ->willReturn(20);
        $this->multiStream->addStream($stream1);
        $this->multiStream->addStream($stream2);
        $this->assertSame(30, $this->multiStream->length);
    }

    public function testGettingLengthWithoutAnySubstreamsReturnsNull(): void
    {
        $this->assertNull($this->multiStream->length);
    }

    public function testIsReadableAlwaysReturnsTrue(): void
    {
        $this->assertTrue($this->multiStream->isReadable);
    }

    public function testIsSeekableOnlyReturnsTrueIfAllStreamsAreSeekable(): void
    {
        $this->assertTrue($this->multiStream->isReadable);
    }

    public function testIsWritableAlwaysReturnsFalse(): void
    {
        $this->assertFalse($this->multiStream->isWritable);
    }

    public function testReadingEmptyStreamReturnsEmptyString(): void
    {
        $this->assertSame('', $this->multiStream->read(123));
    }

    public function testReadingFromMultipleStreamsReadsFirstToEofAndRemainderFromSecond(): void
    {
        $stream1 = $this->createReadableStream();
        $stream2 = $this->createReadableStream();
        $stream1->expects($this->once())
            ->method('read')
            ->with(3)
            ->willReturn('fo');
        $stream2->expects($this->once())
            ->method('read')
            ->with(1)
            ->willReturn('o');
        $this->multiStream->addStream($stream1);
        $this->multiStream->addStream($stream2);
        $this->assertSame('foo', $this->multiStream->read(3));
        $this->assertSame(3, $this->multiStream->position);
    }

    public function testReadingFromSingleStreamReadsThatStream(): void
    {
        $stream = $this->createReadableStream();
        $stream->expects($this->once())
            ->method('read')
            ->with(3)
            ->willReturn('foo');
        $this->multiStream->addStream($stream);
        $this->assertSame('foo', $this->multiStream->read(3));
    }

    public function testReadingToEndWithMultipleStreamsReadsFromCurrentPositionToEnd(): void
    {
        $stream1 = new Stream(\fopen('php://temp', 'r+b'));
        $stream2 = new Stream(\fopen('php://temp', 'r+b'));
        $stream1->write('abc');
        $stream2->write('de');
        $this->multiStream->addStream($stream1);
        $this->multiStream->addStream($stream2);
        $this->multiStream->rewind();
        $this->assertSame('abcde', $this->multiStream->readToEnd());
        $this->assertTrue($this->multiStream->isEof);
        $this->multiStream->seek(1);
        $this->assertSame('bcde', $this->multiStream->readToEnd());
        $this->assertTrue($this->multiStream->isEof);
    }

    public function testReadingToEndWithNoStreamsReturnsEmptyString(): void
    {
        $this->assertSame('', $this->multiStream->readToEnd());
    }

    public function testReadingToEndWithSingleStreamReadsItToEnd(): void
    {
        $stream = new Stream(\fopen('php://temp', 'r+b'));
        $stream->write('foo');
        $this->multiStream->addStream($stream);
        $this->multiStream->seek(1);
        $this->assertSame('oo', $this->multiStream->readToEnd());
        $this->assertTrue($this->multiStream->isEof);
    }

    public function testSeekingFromEndWhenLengthIsNotKnownThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $stream = $this->createReadableStream();
        $stream->method(PropertyHook::get('length'))
            ->willReturn(null);
        $this->multiStream->addStream($stream);
        $this->multiStream->seek(-1, SEEK_END);
    }

    public function testSeekingStreamWithUnknownLengthThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $stream = $this->createReadableStream();
        $stream->method(PropertyHook::get('length'))
            ->willReturn(null);
        $this->multiStream->addStream($stream);
        $this->multiStream->seek(1);
    }

    public function testSeekingUnseekableStreamThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $unseekableStream = $this->createReadableStream();
        $unseekableStream->method(PropertyHook::get('isSeekable'))
            ->willReturn(false);
        $this->multiStream->addStream($unseekableStream);
        $this->multiStream->seek(0);
    }

    public function testSeekingWithInvalidWhenceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Whence -1 is invalid');
        $stream = new Stream(\fopen('php://temp', 'r+b'));
        $stream->write('foo');
        $this->multiStream->addStream($stream);
        $this->multiStream->seek(0, -1);
    }

    public function testSeekingWithMultipleStreamsSeeksToCorrectPosition(): void
    {
        $stream1 = new Stream(\fopen('php://temp', 'r+b'));
        $stream2 = new Stream(\fopen('php://temp', 'r+b'));
        $stream3 = new Stream(\fopen('php://temp', 'r+b'));
        $stream1->write('abc');
        $stream2->write('de');
        $stream3->write('fghij');
        $this->multiStream->addStream($stream1);
        $this->multiStream->addStream($stream2);
        $this->multiStream->addStream($stream3);

        $this->multiStream->seek(1);
        $this->assertSame(1, $stream1->position);
        $this->assertSame(0, $stream2->position);
        $this->assertSame(0, $stream3->position);

        $this->multiStream->seek(3);
        $this->assertSame(3, $stream1->position);
        $this->assertSame(0, $stream2->position);
        $this->assertSame(0, $stream3->position);

        $this->multiStream->seek(4);
        $this->assertSame(3, $stream1->position);
        $this->assertSame(1, $stream2->position);
        $this->assertSame(0, $stream3->position);

        $this->multiStream->seek(5);
        $this->assertSame(3, $stream1->position);
        $this->assertSame(2, $stream2->position);
        $this->assertSame(0, $stream3->position);

        $this->multiStream->seek(6);
        $this->assertSame(3, $stream1->position);
        $this->assertSame(2, $stream2->position);
        $this->assertSame(1, $stream3->position);
    }

    public function testSeekingWithSingleStreamSeeksToCorrectPosition(): void
    {
        $stream = new Stream(\fopen('php://temp', 'r+b'));
        $stream->write('foobar');
        $this->multiStream->addStream($stream);
        $this->multiStream->seek(1);
        $this->assertSame(1, $stream->position);
        $this->multiStream->seek(2, SEEK_CUR);
        $this->assertSame(3, $stream->position);
        $this->multiStream->seek(-1, SEEK_END);
        $this->assertSame(5, $stream->position);
    }

    public function testSeekingWithUnknownLengthThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->multiStream->seek(0);
    }

    public function testToStringRewindsStreamsAndReadsThemToTheEnd(): void
    {
        $stream1 = new Stream(\fopen('php://temp', 'r+b'));
        $stream2 = new Stream(\fopen('php://temp', 'r+b'));
        $stream1->write('foo');
        $stream2->write('bar');
        $stream1->seek(1);
        $stream2->seek(1);
        $this->multiStream->addStream($stream1);
        $this->multiStream->addStream($stream2);
        $this->assertSame('foobar', (string)$this->multiStream);
    }

    public function testToStringWithUnseekableStreamReturnsEmptyString(): void
    {
        $unseekableStream = $this->createReadableStream();
        $unseekableStream->method(PropertyHook::get('isSeekable'))
            ->willReturn(false);
        $unseekableStream->expects($this->never())
            ->method('readToEnd');
        $multiStream = new MultiStream([$unseekableStream]);
        $this->assertEmpty((string)$multiStream);
    }

    public function testWritingThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->multiStream->write('foo');
    }

    /**
     * Creates a readable stream
     *
     * @return IStream&MockObject The readable stream
     */
    private function createReadableStream(): IStream&MockObject
    {
        $stream = $this->createMock(IStream::class);
        $stream->method(PropertyHook::get('isReadable'))
            ->willReturn(true);

        return $stream;
    }
}
