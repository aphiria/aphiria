<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\ContentNegotiation\JsonMediaTypeFormatter;
use Opulence\Net\Tests\Http\Formatting\Mocks\User;
use Opulence\Serialization\JsonSerializer;

/**
 * Tests the JSON media type formatter
 */
class JsonMediaTypeFormatterTest extends \PHPUnit\Framework\TestCase
{
    /** @var JsonMediaTypeFormatter The formatter to use in tests */
    private $formatter;

    public function setUp(): void
    {
        $serializer = new JsonSerializer();
        $this->formatter = new JsonMediaTypeFormatter($serializer);
    }

    public function testCorrectSupportedEncodingsAreReturned(): void
    {
        $this->assertEquals(['utf-8'], $this->formatter->getSupportedEncodings());
    }

    public function testCorrectSupportedMediaTypesAreReturned(): void
    {
        $this->assertEquals(['application/json', 'text/json'], $this->formatter->getSupportedMediaTypes());
    }

    public function testReadingFromStreamDeserializesStreamContents(): void
    {
        $stream = $this->createStreamWithStringBody('{"id":123,"email":"foo@bar.com"}');
        $expectedUser = new User(123, 'foo@bar.com');
        $actualUser = $this->formatter->readFromStream($stream, User::class);
        $this->assertEquals($expectedUser, $actualUser);
    }

    public function testWritingToStreamSetsStreamContentsFromSerializedValue(): void
    {
        $stream = $this->createStreamThatExpectsBody('{"id":123,"email":"foo@bar.com"}');
        $user = new User(123, 'foo@bar.com');
        $this->formatter->writeToStream($user, $stream);
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
