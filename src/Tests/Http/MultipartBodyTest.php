<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Opulence\IO\Streams\IStream;
use Opulence\IO\Streams\MultiStream;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\MultipartBody;
use Opulence\Net\Http\MultipartBodyPart;
use Opulence\Net\Http\StringBody;

/**
 * Tests the multipart body
 */
class MultipartBodyTest extends \PHPUnit\Framework\TestCase
{
    public function testGettingBoundaryReturnsBoundarySpecifiedInConstructor(): void
    {
        $body = new MultipartBody([], 'foo');
        $this->assertEquals('foo', $body->getBoundary());
    }

    public function testGettingBoundaryReturnsUuidWhenNoneSpecifiedInConstructor(): void
    {
        $body = new MultipartBody([]);
        $this->assertNotEmpty($body->getBoundary());
    }

    public function testGettingLengthWithABodyPartWithNullLengthReturnsNull(): void
    {
        $streamMock1 = $this->createMock(IStream::class);
        $streamMock1->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);
        $streamMock1->expects($this->once())
            ->method('getLength')
            ->willReturn(1);
        $body1 = $this->createMock(IHttpBody::class);
        $body1->expects($this->once())
            ->method('readAsStream')
            ->willReturn($streamMock1);
        $body2 = $this->createMock(IHttpBody::class);
        $streamMock2 = $this->createMock(IStream::class);
        $streamMock2->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);
        $streamMock2->expects($this->once())
            ->method('getLength')
            ->willReturn(null);
        $body2->expects($this->once())
            ->method('readAsStream')
            ->willReturn($streamMock2);
        $parts = [
            new MultipartBodyPart(new HttpHeaders, $body1),
            new MultipartBodyPart(new HttpHeaders, $body2)
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertNull($body->getLength());
    }

    public function testGettingLengthWithBodyPartsWithLengthsReturnsSumOfLengths(): void
    {
        $streamMock1 = $this->createMock(IStream::class);
        $streamMock1->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);
        $streamMock1->expects($this->once())
            ->method('getLength')
            ->willReturn(1);
        $body1 = $this->createMock(IHttpBody::class);
        $body1->expects($this->once())
            ->method('readAsStream')
            ->willReturn($streamMock1);
        $body2 = $this->createMock(IHttpBody::class);
        $streamMock2 = $this->createMock(IStream::class);
        $streamMock2->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);
        $streamMock2->expects($this->once())
            ->method('getLength')
            ->willReturn(2);
        $body2->expects($this->once())
            ->method('readAsStream')
            ->willReturn($streamMock2);
        $parts = [
            new MultipartBodyPart(new HttpHeaders, $body1),
            new MultipartBodyPart(new HttpHeaders, $body2)
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
        $this->assertEquals(5 + 4+ 1+ 7 + 4 + 2 + 9, $body->getLength());
    }

    public function testGettingPartsReturnsParts(): void
    {
        $parts = [
            $this->createMultipartBodyPart(['Foo' => 'bar'], 'baz'),
            $this->createMultipartBodyPart(['Oh' => 'hi'], 'mark')
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertSame($parts, $body->getParts());
    }

    public function testNoPartsResultsInOnlyHeaderAndFooter(): void
    {
        $body = new MultipartBody([], '123');
        $this->assertEquals("--123\r\n--123--", (string)$body);
    }

    public function testPartsAreWrittenToStreamWithBoundaries(): void
    {
        $parts = [
            $this->createMultipartBodyPart(['Foo' => 'bar'], 'baz'),
            $this->createMultipartBodyPart(['Oh' => 'hi'], 'mark')
        ];
        $body = new MultipartBody($parts, '123');
        $this->assertEquals("--123\r\nFoo: bar\r\n\r\nbaz\r\n--123\r\nOh: hi\r\n\r\nmark\r\n--123--", (string)$body);
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
        $this->assertEquals("--123\r\nFoo: bar\r\n\r\nbaz\r\n--123--", (string)$body);
    }

    /**
     * Creates a multipart body part for use in tests
     *
     * @param array $rawHeaders The headers to use
     * @param string $body The body to use
     * @return MultipartBodyPart The multipart body part
     */
    private function createMultipartBodyPart(array $rawHeaders, string $body): MultipartBodyPart
    {
        $headers = new HttpHeaders();

        foreach ($rawHeaders as $name => $value) {
            $headers->add($name, $value);
        }

        return new MultipartBodyPart($headers, new StringBody($body));
    }
}
