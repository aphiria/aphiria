<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2017 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Requests;

use Opulence\IO\Streams\IStream;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\Requests\MultipartBodyPart;
use RuntimeException;

/**
 * Tests the multipart body part
 */
class MultipartBodyPartTest extends \PHPUnit\Framework\TestCase
{
    /** The path to copy the body to in tests */
    private const BODY_COPY_PATH = __DIR__ . '/tmp/foo.txt';
    /** @var MultipartBodyPart The body part to use in tests */
    private $bodyPart = null;
    /** @var HttpHeaders The headers to use in tests */
    private $headers = null;
    /** @var IHttpBody|\PHPUnit_Framework_MockObject_MockObject The body to use in tests */
    private $body = null;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->headers = new HttpHeaders();
        $this->body = $this->createMock(IHttpBody::class);
        $this->bodyPart = new MultipartBodyPart($this->headers, $this->body);
    }

    /**
     * Tests that coping a body to a destination that doesn't exist throws an exception
     */
    public function testCopyingBodyToDestinationThatDoesNotExistThrowsException() : void
    {
        $this->expectException(RuntimeException::class);
        $this->bodyPart->copyBody(__DIR__ . '/tmp/doesnotexist.txt');
    }

    /**
     * Tests that coping a body writes its contents to the destination
     */
    public function testCopyingBodyWritesContentsToDestination() : void
    {
        $bodyStream = $this->createMock(IStream::class);
        $bodyStream->expects($this->once())
            ->method('copyToStream');
        $this->body->expects($this->once())
            ->method('readAsStream')
            ->willReturn($bodyStream);
        $this->bodyPart->copyBody(self::BODY_COPY_PATH);
    }

    /**
     * Tests getting body
     */
    public function testGettingBody() : void
    {
        $this->assertSame($this->body, $this->bodyPart->getBody());
    }

    /**
     * Tests getting headers
     */
    public function testGettingHeaders() : void
    {
        $this->assertSame($this->headers, $this->bodyPart->getHeaders());
    }
}
