<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http;

use Opulence\IO\Streams\IStream;

/**
 * Tests the string body
 */
class StringBodyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests reading as a stream returns the same stream instance every time
     */
    public function testReadingAsStreamReturnsSameStreamInstanceEveryTime() : void
    {
        $body = new StringBody('foo');
        $stream = $body->readAsStream();
        $this->assertSame($stream, $stream->readAsStream());
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
