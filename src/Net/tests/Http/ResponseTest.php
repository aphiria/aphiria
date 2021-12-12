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

use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\HttpStatusCode;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\Response;
use Aphiria\Net\Http\StringBody;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public function provideStatusCodes(): array
    {
        return [
            [201, HttpStatusCode::Created],
            [HttpStatusCode::Created, HttpStatusCode::Created]
        ];
    }

    public function testDefaultReasonPhraseIsSet(): void
    {
        $response = new Response(200);
        $this->assertEquals(HttpStatusCode::getDefaultReasonPhrase(200), $response->getReasonPhrase());
    }

    public function testGettingAndSettingBody(): void
    {
        /** @var IBody $body1 */
        $body1 = $this->createMock(IBody::class);
        $response = new Response(200, body: $body1);
        $this->assertSame($body1, $response->getBody());
        /** @var IBody $body2 */
        $body2 = $this->createMock(IBody::class);
        $response->setBody($body2);
        $this->assertSame($body2, $response->getBody());
    }

    /**
     * @dataProvider provideStatusCodes
     */
    public function testGettingAndSettingStatusCode(HttpStatusCode|int $inputStatusCode, HttpStatusCode $expectedStatusCode): void
    {
        $response = new Response();
        $this->assertSame(HttpStatusCode::Ok, $response->getStatusCode());
        $response->setStatusCode($inputStatusCode);
        $this->assertSame($expectedStatusCode, $response->getStatusCode());
    }

    public function testGettingHeaders(): void
    {
        $headers = new Headers();
        $response = new Response(200, $headers);
        $this->assertSame($headers, $response->getHeaders());
    }

    public function testGettingProtocolVersion(): void
    {
        $response = new Response(200, protocolVersion: '2.0');
        $this->assertSame('2.0', $response->getProtocolVersion());
    }

    public function testIntStatusCodeIsConvertedToEnum(): void
    {
        $response = new Response(200);
        $this->assertSame(HttpStatusCode::Ok, $response->getStatusCode());
    }

    public function testInvalidStatusCodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HTTP status code -1');
        new Response(-1);
    }

    public function testMultipleHeaderValuesAreConcatenatedWithCommas(): void
    {
        $response = new Response();
        $response->getHeaders()->add('Foo', 'bar');
        $response->getHeaders()->add('Foo', 'baz', true);
        $this->assertSame("HTTP/1.1 200 OK\r\nFoo: bar, baz\r\n\r\n", (string)$response);
    }

    public function testReasonPhraseIsIncludedOnlyIfDefined(): void
    {
        $response = new Response();
        $response->setStatusCode(200, 'OK');
        $this->assertSame("HTTP/1.1 200 OK\r\n\r\n", (string)$response);
    }

    public function testResponseWithHeadersAndBodyEndsWithBody(): void
    {
        $response = new Response(200, new Headers(), new StringBody('foo'));
        $response->getHeaders()->add('Foo', 'bar');
        $this->assertSame("HTTP/1.1 200 OK\r\nFoo: bar\r\n\r\nfoo", (string)$response);
    }

    public function testResponseWithHeadersButNoBodyEndsWithBlankLine(): void
    {
        $response = new Response();
        $response->getHeaders()->add('Foo', 'bar');
        $this->assertSame("HTTP/1.1 200 OK\r\nFoo: bar\r\n\r\n", (string)$response);
    }

    public function testResponseWithNoHeadersOrBodyEndsWithBlankLine(): void
    {
        $response = new Response();
        $this->assertSame("HTTP/1.1 200 OK\r\n\r\n", (string)$response);
    }

    public function testSettingInvalidStatusCodeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HTTP status code -1');
        (new Response())->setStatusCode(-1);
    }
}
