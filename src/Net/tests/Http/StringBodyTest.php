<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\IO\Streams\IStream;
use Aphiria\Net\Http\StringBody;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StringBodyTest extends TestCase
{
    public function testCastingToStringReturnsContents(): void
    {
        $body = new StringBody('foo');
        $this->assertSame('foo', (string)$body);
    }

    public function testGettingLengthReturnsStringLength(): void
    {
        $body = new StringBody('foo');
        $this->assertSame(3, $body->getLength());
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
        $this->assertSame('foo', $stream->readToEnd());
    }

    public function testReadingAsStringReturnsContents(): void
    {
        $body = new StringBody('foo');
        $this->assertSame('foo', $body->readAsString());
    }

    public function testWritingToStreamActuallyWritesContentsToStream(): void
    {
        /** @var IStream&MockObject $stream */
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('write')
            ->with('foo');
        $body = new StringBody('foo');
        $body->writeToStream($stream);
    }
}
