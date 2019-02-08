<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

namespace Aphiria\Net\Tests\Http;

use Aphiria\Net\Http\StringBody;
use Opulence\IO\Streams\IStream;
use PHPUnit\Framework\MockObject\MockObject;
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
        /** @var IStream|MockObject $stream */
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('write')
            ->with('foo');
        $body = new StringBody('foo');
        $body->writeToStream($stream);
    }
}
