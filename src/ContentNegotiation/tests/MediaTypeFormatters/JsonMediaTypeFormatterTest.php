<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests\MediaTypeFormatters;

use Aphiria\ContentNegotiation\MediaTypeFormatters\JsonMediaTypeFormatter;
use Aphiria\ContentNegotiation\Tests\Mocks\User;
use Aphiria\IO\Streams\IStream;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JsonMediaTypeFormatterTest extends TestCase
{
    private JsonMediaTypeFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new JsonMediaTypeFormatter();
    }

    public function testCanReadAnyType(): void
    {
        $this->assertTrue($this->formatter->canReadType(User::class));
        $this->assertTrue($this->formatter->canReadType('string'));
    }

    public function testCanWriteAnyType(): void
    {
        $this->assertTrue($this->formatter->canWriteType(User::class));
        $this->assertTrue($this->formatter->canWriteType('string'));
    }

    public function testCorrectSupportedEncodingsAreReturned(): void
    {
        $this->assertEquals(['utf-8'], $this->formatter->getSupportedEncodings());
    }

    public function testCorrectSupportedMediaTypesAreReturned(): void
    {
        $this->assertEquals(
            ['application/json', 'text/json', 'application/problem+json'],
            $this->formatter->getSupportedMediaTypes()
        );
    }

    public function testDefaultEncodingReturnsFirstSupportedEncoding(): void
    {
        $this->assertSame('utf-8', $this->formatter->getDefaultEncoding());
    }

    public function testDefaultMediaTypeReturnsFirstSupportedMediaType(): void
    {
        $this->assertSame('application/json', $this->formatter->getDefaultMediaType());
    }

    public function testReadingFromStreamDeserializesStreamContents(): void
    {
        $stream = $this->createStreamWithStringBody('{"id":123,"email":"foo@bar.com"}');
        $expectedUser = new User(123, 'foo@bar.com');
        $actualUser = $this->formatter->readFromStream($stream, User::class);
        $this->assertEquals($expectedUser, $actualUser);
    }

    public function testWritingArrayOfObjectsIsSuccessful(): void
    {
        $stream = $this->createStreamThatExpectsBody('[{"email":"foo@bar.com","id":123}]');
        $user = new User(123, 'foo@bar.com');
        $this->formatter->writeToStream([$user], $stream, 'utf-8');
    }

    public function testWritingToStreamSetsStreamContentsFromSerializedValue(): void
    {
        $stream = $this->createStreamThatExpectsBody('{"email":"foo@bar.com","id":123}');
        $user = new User(123, 'foo@bar.com');
        $this->formatter->writeToStream($user, $stream, 'utf-8');
    }

    public function testWritingUsingUnsupportedEncodingThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('foo is not supported for %s', JsonMediaTypeFormatter::class));
        $user = new User(123, 'foo@bar.com');
        $this->formatter->writeToStream($user, $this->createMock(IStream::class), 'foo');
    }

    public function testWritingWithNullEncodingUsesDefaultEncoding(): void
    {
        $stream = $this->createMock(IStream::class);
        $user = new User(123, 'foo@bar.com');
        $expectedEncodedValue = \mb_convert_encoding('{"email":"foo@bar.com","id":123}', 'utf-8');
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
    private function createStreamThatExpectsBody(string $body): IStream|MockObject
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
    private function createStreamWithStringBody(string $body): IStream|MockObject
    {
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn($body);

        return $stream;
    }
}
