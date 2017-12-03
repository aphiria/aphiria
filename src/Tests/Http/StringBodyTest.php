<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\StringBody;

/**
 * Tests the string body
 */
class StringBodyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests casting to a string returns the contents
     */
    public function testCastingToStringReturnsContents() : void
    {
        $body = new StringBody('foo');
        $this->assertEquals('foo', (string)$body);
    }

    /**
     * Tests reading as a stream returns the same stream instance every time
     */
    public function testReadingAsStreamReturnsSameStreamInstanceEveryTime() : void
    {
        $body = new StringBody('foo');
        $expectedStream = $body->readAsStream();
        $this->assertSame($expectedStream, $body->readAsStream());
    }

    /**
     * Tests reading as a stream returns the body contents' written to a stream
     */
    public function testReadingAsStreamReturnsStreamWithContentsWrittenToIt() : void
    {
        $body = new StringBody('foo');
        $stream = $body->readAsStream();
        $this->assertEquals('foo', $stream->readToEnd());
    }

    /**
     * Tests reading as a string returns the contents
     */
    public function testReadingAsStringReturnsContents() : void
    {
        $body = new StringBody('foo');
        $this->assertEquals('foo', $body->readAsString());
    }

    /**
     * Tests writing to a stream actually writes the contents to the stream
     */
    public function testWritingToStreamActuallyWritesContentsToStream() : void
    {
        $stream = $this->createMock(IStream::class);
        $stream->expects($this->once())
            ->method('write')
            ->with('foo');
        $body = new StringBody('foo');
        $body->writeToStream($stream);
    }
}
