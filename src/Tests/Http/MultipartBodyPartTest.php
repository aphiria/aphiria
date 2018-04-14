<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\MultipartBodyPart;

/**
 * Tests the multipart body part
 */
class MultipartBodyPartTest extends \PHPUnit\Framework\TestCase
{
    /** @var MultipartBodyPart The body part to use in tests */
    private $bodyPart;
    /** @var HttpHeaders The headers to use in tests */
    private $headers;
    /** @var IHttpBody|\PHPUnit_Framework_MockObject_MockObject The body to use in tests */
    private $body;

    public function setUp(): void
    {
        $this->headers = new HttpHeaders();
        $this->body = $this->createMock(IHttpBody::class);
        $this->bodyPart = new MultipartBodyPart($this->headers, $this->body);
    }

    public function testGettingBody(): void
    {
        $this->assertSame($this->body, $this->bodyPart->getBody());
    }

    public function testGettingHeaders(): void
    {
        $this->assertSame($this->headers, $this->bodyPart->getHeaders());
    }

    public function testSerializingSeparatesHeadersAndBodyWithTwoEmptyLines(): void
    {
        $this->headers->add('Foo', 'bar');
        $this->body->expects($this->once())
            ->method('__toString')
            ->willReturn('baz');
        $this->assertEquals("Foo: bar\r\n\r\nbaz", (string)$this->bodyPart);
    }
}
