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

use Aphiria\IO\Streams\Stream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class StreamTest extends TestCase
{
    /** @const A temporary file to use for non read/write stream tests */
    private const string TEMP_FILE = __DIR__ . '/temp.txt';

    protected function tearDown(): void
    {
        if (\file_exists(self::TEMP_FILE)) {
            @\unlink(self::TEMP_FILE);
        }
    }

    public function testCastingToStringOnClosedStreamReturnsEmptyString(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);
        $stream->write('foo');
        $stream->close();
        $this->assertSame('', (string)$stream);
    }

    public function testCastingToStringRewindsAndReadsToEnd(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);
        $stream->write('foo');
        $stream->read(1);
        $this->assertSame('foo', (string)$stream);
    }

    public function testClosingStreamUnsetsResource(): void
    {
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle);
        $stream->close();
        $this->assertFalse(\is_resource($handle));
    }

    public function testCopyingToClosedStreamThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $sourceStream = new Stream(\fopen('php://temp', 'r+b'));
        $sourceStream->write('foo');
        $sourceStream->rewind();
        $destinationStream = new Stream(\fopen('php://temp', 'r+b'));
        $destinationStream->close();
        $sourceStream->copyToStream($destinationStream, 1);
    }

    public function testCopyingToStreamCopiesAllContentsUsingBufferSize(): void
    {
        $sourceStream = new Stream(\fopen('php://temp', 'r+b'));
        $sourceStream->write('foo');
        $sourceStream->rewind();
        $destinationStream = new Stream(\fopen('php://temp', 'r+b'));
        $sourceStream->copyToStream($destinationStream, 1);
        $destinationStream->rewind();
        $this->assertSame('foo', $destinationStream->readToEnd());
    }

    public function testDestructorUnsetsResource(): void
    {
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle);
        unset($stream);
        $this->assertFalse(\is_resource($handle));
    }

    public function testGettingLengthOfClosedStreamThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle, 724);
        $stream->close();
        $stream->length;
    }

    public function testGettingPositionThrowsExceptionIfStreamIsClosed(): void
    {
        $this->expectException(RuntimeException::class);
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle);
        $stream->close();
        $stream->length;
    }

    public function testIsEofReturnsFalseForStreamsThatAreNotAtEof(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);
        $stream->write('foo');
        $this->assertFalse($stream->isEof);
    }

    public function testIsEofReturnsTrueForStreamsAtEof(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);
        $stream->write('foo');
        $stream->readToEnd();
        $this->assertTrue($stream->isEof);
    }

    public function testIsEofThrowsExceptionForClosedStream(): void
    {
        $this->expectException(RuntimeException::class);
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);
        $stream->write('foo');
        $stream->close();
        $stream->isEof;
    }

    public function testIsReadableReturnsCorrectValueBasedOnItsMode(): void
    {
        $readableHandle = \fopen('php://temp', 'rb');
        $readableStream = new Stream($readableHandle);
        $this->assertTrue($readableStream->isReadable);
        $unreadableHandle = \fopen(self::TEMP_FILE, 'wb');
        $unreadableStream = new Stream($unreadableHandle);
        $this->assertFalse($unreadableStream->isReadable);
    }

    public function testIsSeekableReturnsCorrectValueBasedOnItsMode(): void
    {
        $seekableHandle = \fopen('php://temp', 'r+b');
        $seekableStream = new Stream($seekableHandle);
        $this->assertTrue($seekableStream->isSeekable);
        // Testing unseekable streams is not possible
    }

    public function testIsWritableReturnsCorrectValueBasedOnItsMode(): void
    {
        $writableHandle = \fopen('php://temp', 'wb');
        $writableStream = new Stream($writableHandle);
        $this->assertTrue($writableStream->isWritable);
        $unwritableHandle = \fopen('php://temp', 'rb');
        $unwritableStream = new Stream($unwritableHandle);
        $this->assertFalse($unwritableStream->isWritable);
    }

    public function testKnownLengthOfStreamIsAlwaysReturned(): void
    {
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle, 724);
        $this->assertSame(724, $stream->length);
    }

    public function testNonResourceThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress InvalidArgument Purposely testing this scenario */
        new Stream(123);
    }

    public function testPositionReturnsCorrectPositionAfterWriting(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);
        $stream->write('foo');
        $this->assertSame(3, $stream->position);
    }

    public function testReadingFromClosedStreamThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);
        $stream->write('foo');
        $stream->close();
        $stream->read(1);
    }

    public function testReadingFromUnreadableStreamThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $handle = \fopen(self::TEMP_FILE, 'ab');
        $stream = new Stream($handle);
        $stream->write('foo');
        $stream->read(1);
    }

    public function testReadingToEndFromClosedStreamThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);
        $stream->write('foo');
        $stream->close();
        $stream->readToEnd();
    }

    public function testReadingToEndFromUnreadableStreamThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $handle = \fopen(self::TEMP_FILE, 'ab');
        $stream = new Stream($handle);
        $stream->write('foo');
        $stream->readToEnd();
    }

    public function testResourceLengthIsReturnedWhenLengthIsNotKnownAheadOfTime(): void
    {
        $handle = \fopen('php://temp', 'rb');
        $expectedLength = \fstat($handle)['size'];
        $stream = new Stream($handle);
        $this->assertSame($expectedLength, $stream->length);
    }

    public function testRewindSeeksToBeginningOfStream(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);
        $stream->write('foo');
        $this->assertSame('', $stream->readToEnd());
        $stream->rewind();
        $this->assertSame('foo', $stream->readToEnd());
    }

    public function testSeekingChangesPosition(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);
        $stream->write('foo');
        $stream->rewind();
        $stream->seek(1);
        $this->assertSame('oo', $stream->readToEnd());
        $stream->rewind();
        $stream->seek(2);
        $this->assertSame('o', $stream->readToEnd());
    }

    public function testWritingToStreamWritesData(): void
    {
        $handle = \fopen('php://temp', 'w+b');
        $stream = new Stream($handle);
        $stream->write('foo');
        $stream->rewind();
        $this->assertSame('foo', $stream->readToEnd());
    }

    public function testWritingToUnwritableStreamThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $handle = \fopen('php://temp', 'rb');
        $stream = new Stream($handle);
        $stream->write('foo');
    }
}
