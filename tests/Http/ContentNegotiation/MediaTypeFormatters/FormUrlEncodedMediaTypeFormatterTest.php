<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Tests\Http\Formatting;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\FormUrlEncodedSerializerMediaTypeFormatter;
use Aphiria\Net\Tests\Http\Formatting\Mocks\User;
use Aphiria\Serialization\FormUrlEncodedSerializer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the form URL-encoded media type formatter
 */
class FormUrlEncodedMediaTypeFormatterTest extends TestCase
{
    /** @var FormUrlEncodedSerializerMediaTypeFormatter The formatter to use in tests */
    private $formatter;

    public function setUp(): void
    {
        $serializer = new FormUrlEncodedSerializer();
        $this->formatter = new FormUrlEncodedSerializerMediaTypeFormatter($serializer);
    }

    public function testCanReadOnlyObjectsAndArrays(): void
    {
        $this->assertTrue($this->formatter->canReadType(User::class));
        $this->assertTrue($this->formatter->canReadType('array'));
        $this->assertTrue($this->formatter->canReadType('string[]'));
        $this->assertTrue($this->formatter->canReadType(User::class . '[]'));
        $this->assertFalse($this->formatter->canReadType('string'));
    }

    public function testCanWriteOnlyObjectsAndArrays(): void
    {
        $this->assertTrue($this->formatter->canWriteType(User::class));
        $this->assertTrue($this->formatter->canWriteType('array'));
        $this->assertTrue($this->formatter->canWriteType('string[]'));
        $this->assertTrue($this->formatter->canWriteType(User::class . '[]'));
        $this->assertFalse($this->formatter->canWriteType('string'));
    }

    public function testCorrectSupportedEncodingsAreReturned(): void
    {
        $this->assertEquals(['utf-8', 'ISO-8859-1'], $this->formatter->getSupportedEncodings());
    }

    public function testCorrectSupportedMediaTypesAreReturned(): void
    {
        $this->assertEquals(['application/x-www-form-urlencoded'], $this->formatter->getSupportedMediaTypes());
    }

    public function testDefaultEncodingReturnsFirstSupportedEncoding(): void
    {
        $this->assertEquals('utf-8', $this->formatter->getDefaultEncoding());
    }

    public function testDefaultMediaTypeReturnsFirstSupportedMediaType(): void
    {
        $this->assertEquals('application/x-www-form-urlencoded', $this->formatter->getDefaultMediaType());
    }

    public function testReadingFromStreamDeserializesStreamContents(): void
    {
        $stream = $this->createStreamWithStringBody('id=123&email=foo%40bar.com');
        $expectedUser = new User(123, 'foo@bar.com');
        $actualUser = $this->formatter->readFromStream($stream, User::class);
        $this->assertEquals($expectedUser, $actualUser);
    }

    public function testReadingTypeThatCannotBeReadThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('% s cannot read type string', FormUrlEncodedSerializerMediaTypeFormatter::class));
        $stream = $this->createMock(IStream::class);
        $this->formatter->readFromStream($stream, 'string');
    }

    public function testWritingArrayOfObjectsIsSuccessful(): void
    {
        $stream = $this->createStreamThatExpectsBody('0%5Bid%5D=123&0%5Bemail%5D=foo%40bar.com');
        $user = new User(123, 'foo@bar.com');
        $this->formatter->writeToStream([$user], $stream, 'utf-8');
    }

    public function testWritingToStreamSetsStreamContentsFromSerializedValue(): void
    {
        $stream = $this->createStreamThatExpectsBody('id=123&email=foo%40bar.com');
        $user = new User(123, 'foo@bar.com');
        $this->formatter->writeToStream($user, $stream, 'utf-8');
    }

    public function testWritingConvertsToInputEncoding(): void
    {
        $stream = $this->createMock(IStream::class);
        $user = new User(123, 'foo@bar.com');
        $expectedEncodedValue = \mb_convert_encoding('id=123&email=foo%40bar.com', 'ISO-8859-1');
        $stream->expects($this->once())
            ->method('write')
            ->with($expectedEncodedValue);
        $this->formatter->writeToStream($user, $stream, 'ISO-8859-1');
    }

    public function testWritingTypeThatCannotBeWrittenThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('%s cannot write type string', FormUrlEncodedSerializerMediaTypeFormatter::class));
        $this->formatter->writeToStream('foo', $this->createMock(IStream::class), null);
    }

    public function testWritingUsingUnsupportedEncodingThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('foo is not supported for %s', FormUrlEncodedSerializerMediaTypeFormatter::class));
        $user = new User(123, 'foo@bar.com');
        $this->formatter->writeToStream($user, $this->createMock(IStream::class), 'foo');
    }

    public function testWritingWithNullEncodingUsesDefaultEncoding(): void
    {
        $stream = $this->createMock(IStream::class);
        $user = new User(123, 'foo@bar.com');
        $expectedEncodedValue = \mb_convert_encoding('id=123&email=foo%40bar.com', 'utf-8');
        $stream->expects($this->once())
            ->method('write')
            ->with($expectedEncodedValue);
        $this->formatter->writeToStream($user, $stream, null);
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
