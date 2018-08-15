<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\ContentNegotiation\FormUrlEncodedSerializerMediaTypeFormatter;
use Opulence\Net\Tests\Http\Formatting\Mocks\User;
use Opulence\Serialization\FormUrlEncodedSerializer;

/**
 * Tests the form URL-encoded media type formatter
 */
class FormUrlEncodedMediaTypeFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var FormUrlEncodedSerializerMediaTypeFormatter The formatter to use in tests */
    private $formatter;

    public function setUp(): void
    {
        $serializer = new FormUrlEncodedSerializer();
        $this->formatter = new FormUrlEncodedSerializerMediaTypeFormatter($serializer);
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

    public function testWritingUsingUnsupportedEncodingThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
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
     * @return IStream|\PHPUnit_Framework_MockObject_MockObject The stream that expects the input body
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
     * @return IStream|\PHPUnit_Framework_MockObject_MockObject The stream with the input body as its string body
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
