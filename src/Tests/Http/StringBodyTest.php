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
use Opulence\Net\Http\StringBody;
use PHPUnit\Framework\TestCase;

/**
 * Tests the string body
 */
class StringBodyTest extends TestCase
{
    public function testCastingToStringReturnsContents(): void
    {
        $body = new StringBody('foo');
        $this->assertEquals('foo', (string)$body);
    }

    public function testGettingLengthReturnsStringLength(): void
    {
        $body = new StringBody('foo');
        $this->assertEquals(3, $body->getLength());
    }

    public function testReadingAsStreamReturnsSameStreamInstanceEveryTime(): void
    {
        $body = new StringBody('foo');
        $expectedStream = $body->readAsStream();
        $this->assertSame($expectedStream, $body->readAsStream());
    }

    public function testReadingAsStreamReturnsStreamWithContentsWrittenToIt(): void
    {
        $body = new StringBody('foo');
        $stream = $body->readAsStream();
        $this->assertEquals('foo', $stream->readToEnd());
    }

    public function testReadingAsStringReturnsContents(): void
    {
        $body = new StringBody('foo');
        $this->assertEquals('foo', $body->readAsString());
    }

    public function testWritingToStreamActuallyWritesContentsToStream(): void
    {
        /** @var IStream|\PHPUnit_Framework_MockObject_MockObject $stream */
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('write')
            ->with('foo');
        $body = new StringBody('foo');
        $body->writeToStream($stream);
    }
}
