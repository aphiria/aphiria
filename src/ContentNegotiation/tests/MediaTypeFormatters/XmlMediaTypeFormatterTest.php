<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests\MediaTypeFormatters;

use Aphiria\ContentNegotiation\MediaTypeFormatters\XmlMediaTypeFormatter;
use Aphiria\ContentNegotiation\Tests\Mocks\User;
use Aphiria\IO\Streams\IStream;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class XmlMediaTypeFormatterTest extends TestCase
{
    private XmlMediaTypeFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new XmlMediaTypeFormatter();
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
        $this->assertEquals(['utf-8', 'utf-16', 'iso-8859'], $this->formatter->supportedEncodings);
    }

    public function testCorrectSupportedMediaTypesAreReturned(): void
    {
        $this->assertEquals(
            ['text/xml', 'application/problem+xml'],
            $this->formatter->supportedMediaTypes
        );
    }

    public function testDefaultEncodingReturnsFirstSupportedEncoding(): void
    {
        $this->assertSame('utf-8', $this->formatter->defaultEncoding);
    }

    public function testDefaultMediaTypeReturnsFirstSupportedMediaType(): void
    {
        $this->assertSame('text/xml', $this->formatter->defaultMediaType);
    }

    public function testReadingFromStreamDeserializesStreamContents(): void
    {
        $stream = $this->createStreamWithStringBody('<user><id>123</id><email>foo@bar.com</email></user>');
        $expectedUser = new User(123, 'foo@bar.com');
        $actualUser = $this->formatter->readFromStream($stream, User::class);
        $this->assertEquals($expectedUser, $actualUser);
    }

    public function testWritingArrayOfObjectsIsSuccessful(): void
    {
        $xml = '<?xml version="1.0"?>' . \PHP_EOL . '<response><item key="0"><id>123</id><email>foo@bar.com</email></item></response>' . \PHP_EOL;
        $stream = $this->createStreamThatExpectsBody($xml);
        $user = new User(123, 'foo@bar.com');
        $this->formatter->writeToStream([$user], $stream, 'utf-8');
    }

    public function testWritingToStreamSetsStreamContentsFromSerializedValue(): void
    {
        $xml = '<?xml version="1.0"?>' . \PHP_EOL . '<response><id>123</id><email>foo@bar.com</email></response>' . \PHP_EOL;
        $stream = $this->createStreamThatExpectsBody($xml);
        $user = new User(123, 'foo@bar.com');
        $this->formatter->writeToStream($user, $stream, 'utf-8');
    }

    public function testWritingUsingUnsupportedEncodingThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('foo is not supported for %s', XmlMediaTypeFormatter::class));
        $user = new User(123, 'foo@bar.com');
        $this->formatter->writeToStream($user, $this->createMock(IStream::class), 'foo');
    }

    public function testWritingWithNullEncodingUsesDefaultEncoding(): void
    {
        $stream = $this->createMock(IStream::class);
        $user = new User(123, 'foo@bar.com');
        $xml = '<?xml version="1.0"?>' . \PHP_EOL . '<response><id>123</id><email>foo@bar.com</email></response>' . \PHP_EOL;
        $expectedEncodedValue = \mb_convert_encoding($xml, 'utf-8');
        $stream->expects($this->once())
            ->method('write')
            ->with($expectedEncodedValue);
        $this->formatter->writeToStream($user, $stream, null);
    }

    /**
     * Creates a stream with an expected body that will be written to it
     *
     * @param string $body The expected body of the stream
     * @return IStream&MockObject The stream that expects the input body
     */
    private function createStreamThatExpectsBody(string $body): IStream&MockObject
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
     * @return IStream&MockObject The stream with the input body as its string body
     */
    private function createStreamWithStringBody(string $body): IStream&MockObject
    {
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('__toString')
            ->willReturn($body);

        return $stream;
    }
}
