<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests\MediaTypeFormatters;

use Aphiria\ContentNegotiation\MediaTypeFormatters\PlainTextMediaTypeFormatter;
use Aphiria\ContentNegotiation\Tests\Mocks\User;
use Aphiria\IO\Streams\IStream;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PlainTextMediaTypeFormatterTest extends TestCase
{
    private PlainTextMediaTypeFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new PlainTextMediaTypeFormatter();
    }

    public function testCanReadOnlyStrings(): void
    {
        $this->assertTrue($this->formatter->canReadType('string'));
        $this->assertFalse($this->formatter->canReadType(User::class));
    }

    public function testCanWriteOnlyStrings(): void
    {
        $this->assertTrue($this->formatter->canReadType('string'));
        $this->assertFalse($this->formatter->canReadType(User::class));
    }

    public function testCorrectSupportedEncodingsAreReturned(): void
    {
        $this->assertEquals(['utf-8'], $this->formatter->getSupportedEncodings());
    }

    public function testCorrectSupportedMediaTypesAreReturned(): void
    {
        $this->assertEquals(['text/plain'], $this->formatter->getSupportedMediaTypes());
    }

    public function testDefaultEncodingReturnsFirstSupportedEncoding(): void
    {
        $this->assertSame('utf-8', $this->formatter->getDefaultEncoding());
    }

    public function testDefaultMediaTypeReturnsFirstSupportedMediaType(): void
    {
        $this->assertSame('text/plain', $this->formatter->getDefaultMediaType());
    }

    public function testReadingAsArrayOfStringsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('%s can only read strings', PlainTextMediaTypeFormatter::class));
        $this->formatter->readFromStream($this->createMock(IStream::class), 'string[]');
    }

    public function testReadingFromStreamReturnsSerializedStream(): void
    {
        $stream = $this->createStreamWithStringBody('foo');
        $value = $this->formatter->readFromStream($stream, 'string');
        $this->assertSame('foo', $value);
    }

    public function testReadingNonStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('%s can only read strings', PlainTextMediaTypeFormatter::class));
        $this->formatter->readFromStream($this->createMock(IStream::class), self::class);
    }

    public function testReadingTypeThatCannotBeReadThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('%s can only read strings', PlainTextMediaTypeFormatter::class));
        $stream = $this->createMock(IStream::class);
        $this->formatter->readFromStream($stream, User::class);
    }

    public function testWritingNonStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('%s can only write strings', PlainTextMediaTypeFormatter::class));
        $this->formatter->writeToStream($this, $this->createMock(IStream::class), 'utf-8');
    }

    public function testWritingToStreamSerializesInput(): void
    {
        $stream = $this->createStreamThatExpectsBody('foo');
        $this->formatter->writeToStream('foo', $stream, 'utf-8');
    }

    public function testWritingTypeThatCannotBeWrittenThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('%s can only write strings', PlainTextMediaTypeFormatter::class));
        $this->formatter->writeToStream(new User(123, 'foo@bar.com'), $this->createMock(IStream::class), null);
    }

    public function testWritingUsingUnsupportedEncodingThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(PlainTextMediaTypeFormatter::class);
        $this->formatter->writeToStream('foo', $this->createMock(IStream::class), 'bar');
    }

    public function testWritingWithNullEncodingUsesDefaultEncoding(): void
    {
        $stream = $this->createMock(IStream::class);
        $expectedEncodedValue = \mb_convert_encoding('‡', 'utf-8');
        $stream->expects($this->once())
            ->method('write')
            ->with($expectedEncodedValue);
        $this->formatter->writeToStream('‡', $stream, null);
    }

    /**
     * Creates a stream with an expected body that will be written to it
     *
     * @param string $body The expected body of the stream
     * @return IStream|MockObject The stream that expects the input body
     */
    private function createStreamThatExpectsBody(string $body): IStream
    {
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('write')
            ->with($body);

        return $stream;
    }

    /**
     * Creates a stream with a string body
     *
     * @param string $body The body of the stream
     * @return IStream|MockObject The stream with the input body as its string body
     */
    private function createStreamWithStringBody(string $body): IStream
    {
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn($body);

        return $stream;
    }
}
