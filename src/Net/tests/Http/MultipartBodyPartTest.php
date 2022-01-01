<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\MultipartBodyPart;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MultipartBodyPartTest extends TestCase
{
    private MultipartBodyPart $bodyPart;
    private Headers $headers;
    private IBody&MockObject $body;

    protected function setUp(): void
    {
        $this->headers = new Headers();
        $this->body = $this->createMock(IBody::class);
        $this->bodyPart = new MultipartBodyPart($this->headers, $this->body);
    }

    public function testGettingBody(): void
    {
        $this->assertSame($this->body, $this->bodyPart->body);
    }

    public function testGettingHeaders(): void
    {
        $this->assertSame($this->headers, $this->bodyPart->headers);
    }

    public function testSerializingSeparatesHeadersAndBodyWithTwoEmptyLines(): void
    {
        $this->headers->add('Foo', 'bar');
        $this->body->expects($this->once())
            ->method('__toString')
            ->willReturn('baz');
        $this->assertSame("Foo: bar\r\n\r\nbaz", (string)$this->bodyPart);
    }
}
