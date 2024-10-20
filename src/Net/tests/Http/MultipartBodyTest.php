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
use Aphiria\IO\Streams\MultiStream;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\MultipartBody;
use Aphiria\Net\Http\MultipartBodyPart;
use Aphiria\Net\Http\StringBody;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;

class MultipartBodyTest extends TestCase
{
    public function testGettingBoundaryReturnsBoundarySpecifiedInConstructor(): void
    {
        $body = new MultipartBody([], 'foo');
        $this->assertSame('foo', $body->boundary);
    }

    public function testGettingBoundaryReturnsUuidWhenNoneSpecifiedInConstructor(): void
    {
        $body = new MultipartBody([]);
        $this->assertNotEmpty($body->boundary);
    }

    public function testGettingLengthWithABodyPartWithNullLengthReturnsNull(): void
    {
        $streamMock1 = $this->createMock(IStream::class);
        $streamMock1->method(PropertyHook::get('isReadable'))
            ->willReturn(true);
        $streamMock1->method(PropertyHook::get('length'))
            ->willReturn(1);
        $body1 = $this->createMock(IBody::class);
        $body1->expects($this->once())
            ->method('readAsStream')
            ->willReturn($streamMock1);
        $body2 = $this->createMock(IBody::class);
        $streamMock2 = $this->createMock(IStream::class);
        $streamMock2->method(PropertyHook::get('isReadable'))
            ->willReturn(true);
        $streamMock2->method(PropertyHook::get('length'))
            ->willReturn(null);
        $body2->expects($this->once())
            ->method('readAsStream')
            ->willReturn($streamMock2);
        $parts = [
            new MultipartBodyPart(new Headers(), $body1),
            new MultipartBodyPart(new Headers(), $body2)
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertNull($body->length);
    }

    public function testGettingLengthWithBodyPartsWithLengthsReturnsSumOfLengths(): void
    {
        $streamMock1 = $this->createMock(IStream::class);
        $streamMock1->method(PropertyHook::get('isReadable'))
            ->willReturn(true);
        $streamMock1->method(PropertyHook::get('length'))
            ->willReturn(1);
        $body1 = $this->createMock(IBody::class);
        $body1->expects($this->once())
            ->method('readAsStream')
            ->willReturn($streamMock1);
        $body2 = $this->createMock(IBody::class);
        $streamMock2 = $this->createMock(IStream::class);
        $streamMock2->method(PropertyHook::get('isReadable'))
            ->willReturn(true);
        $streamMock2->method(PropertyHook::get('length'))
            ->willReturn(2);
        $body2->expects($this->once())
            ->method('readAsStream')
            ->willReturn($streamMock2);
        $parts = [
            new MultipartBodyPart(new Headers(), $body1),
            new MultipartBodyPart(new Headers(), $body2)
        ];
        $body = new MultipartBody($parts, '123');
        /**
         * Expected stream looks like:
         * --{boundary}
         * \r\n\r\n
         * stream 1
         * \r\n--{boundary}
         * \r\n\r\n
         * stream 2
         * \r\n--{boundary}--
         */
        $this->assertSame(5 + 4 + 1 + 7 + 4 + 2 + 9, $body->length);
    }

    public function testGettingPartsReturnsParts(): void
    {
        $parts = [
            $this->createMultipartBodyPart(['Foo' => 'bar'], 'baz'),
            $this->createMultipartBodyPart(['Oh' => 'hi'], 'mark')
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertSame($parts, $body->parts);
    }

    public function testNoPartsResultsInOnlyHeaderAndFooter(): void
    {
        $body = new MultipartBody([], '123');
        $this->assertSame("--123\r\n--123--", (string)$body);
    }

    public function testPartsAreWrittenToStreamWithBoundaries(): void
    {
        $parts = [
            $this->createMultipartBodyPart(['Foo' => 'bar'], 'baz'),
            $this->createMultipartBodyPart(['Oh' => 'hi'], 'mark')
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertSame("--123\r\nFoo: bar\r\n\r\nbaz\r\n--123\r\nOh: hi\r\n\r\nmark\r\n--123--", (string)$body);
    }

    public function testReadingAsStreamReturnsAMultiStream(): void
    {
        $parts = [
            $this->createMultipartBodyPart(['Foo' => 'bar'], 'baz'),
            $this->createMultipartBodyPart(['Oh' => 'hi'], 'mark')
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertInstanceOf(MultiStream::class, $body->readAsStream());
    }

    public function testSinglePartIsWrappedWithHeaderAndFooter(): void
    {
        $parts = [
            $this->createMultipartBodyPart(['Foo' => 'bar'], 'baz')
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertSame("--123\r\nFoo: bar\r\n\r\nbaz\r\n--123--", (string)$body);
    }

    /**
     * Creates a multipart body part for use in tests
     *
     * @param array<string, mixed> $rawHeaders The headers to use
     * @param string $body The body to use
     * @return MultipartBodyPart The multipart body part
     */
    private function createMultipartBodyPart(array $rawHeaders, string $body): MultipartBodyPart
    {
        $headers = new Headers();

        foreach ($rawHeaders as $name => $value) {
            $headers->add($name, $value);
        }

        return new MultipartBodyPart($headers, new StringBody($body));
    }
}
