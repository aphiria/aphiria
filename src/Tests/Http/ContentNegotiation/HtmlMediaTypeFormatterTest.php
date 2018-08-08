<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\ContentNegotiation;

use InvalidArgumentException;
use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\ContentNegotiation\HtmlMediaTypeFormatter;

/**
 * Tests the HTML media type formatter
 */
class HtmlMediaTypeFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var HtmlMediaTypeFormatter The formatter to use in tests */
    private $formatter;

    public function setUp(): void
    {
        $this->formatter = new HtmlMediaTypeFormatter();
    }

    public function testCorrectSupportedEncodingsAreReturned(): void
    {
        $this->assertEquals(['utf-8', 'utf-16'], $this->formatter->getSupportedEncodings());
    }

    public function testCorrectSupportedMediaTypesAreReturned(): void
    {
        $this->assertEquals(['text/html'], $this->formatter->getSupportedMediaTypes());
    }

    public function testReadingAsArrayOfStringsThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->formatter->readFromStream($this->createMock(IStream::class), 'string', true);
    }

    public function testReadingFromStreamReturnsSerializedStream(): void
    {
        $stream = $this->createStreamWithStringBody('foo');
        $value = $this->formatter->readFromStream($stream, 'string');
        $this->assertEquals('foo', $value);
    }

    public function testReadingNonStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->formatter->readFromStream($this->createMock(IStream::class), self::class);
    }

    public function testWritingNonStringThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->formatter->writeToStream($this, $this->createMock(IStream::class), 'utf-8');
    }

    public function testWritingToStreamSerializesInput(): void
    {
        $stream = $this->createStreamThatExpectsBody('foo');
        $this->formatter->writeToStream('foo', $stream, 'utf-8');
    }

    public function testWritingConvertsToInputEncoding(): void
    {
        $stream = $this->createMock(IStream::class);
        $expectedEncodedValue = \mb_convert_encoding('‡', 'utf-16');
        $stream->expects($this->once())
            ->method('write')
            ->with($expectedEncodedValue);
        $this->formatter->writeToStream('‡', $stream, 'utf-16');
    }

    public function testWritingUsingUnsupportedEncodingThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->formatter->writeToStream('foo', $this->createMock(IStream::class), 'bar');
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
