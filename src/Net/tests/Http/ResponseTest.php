<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http;

use Aphiria\Net\Http\HttpHeaders;
use Aphiria\Net\Http\HttpStatusCodes;
use Aphiria\Net\Http\IHttpBody;
use Aphiria\Net\Http\Response;
use PHPUnit\Framework\TestCase;

/**
 * Tests the response class
 */
class ResponseTest extends TestCase
{
    public function testDefaultReasonPhraseIsSet(): void
    {
        $response = new Response(200);
        $this->assertEquals(HttpStatusCodes::getDefaultReasonPhrase(200), $response->getReasonPhrase());
    }

    public function testGettingAndSettingBody(): void
    {
        /** @var IHttpBody $body1 */
        $body1 = $this->createMock(IHttpBody::class);
        $response = new Response(200, null, $body1);
        $this->assertSame($body1, $response->getBody());
        /** @var IHttpBody $body2 */
        $body2 = $this->createMock(IHttpBody::class);
        $response->setBody($body2);
        $this->assertSame($body2, $response->getBody());
    }

    public function testGettingAndSettingStatusCode(): void
    {
        $response = new Response(201);
        $this->assertEquals(201, $response->getStatusCode());
        $response->setStatusCode(202);
        $this->assertEquals(202, $response->getStatusCode());
    }

    public function testGettingHeaders(): void
    {
        $headers = new HttpHeaders();
        $response = new Response(200, $headers);
        $this->assertSame($headers, $response->getHeaders());
    }

    public function testGettingProtocolVersion(): void
    {
        $response = new Response(200, null, null, '2.0');
        $this->assertEquals('2.0', $response->getProtocolVersion());
    }

    public function testMultipleHeaderValuesAreConcatenatedWithCommas(): void
    {
        $response = new Response();
        $response->getHeaders()->add('Foo', 'bar');
        $response->getHeaders()->add('Foo', 'baz', true);
        $this->assertEquals("HTTP/1.1 200 OK\r\nFoo: bar, baz\r\n\r\n", (string)$response);
    }

    public function testReasonPhraseIsIncludedOnlyIfDefined(): void
    {
        $response = new Response();
        $response->setStatusCode(200, 'OK');
        $this->assertEquals("HTTP/1.1 200 OK\r\n\r\n", (string)$response);
    }

    public function testResponseWithHeadersButNoBodyEndsWithBlankLine(): void
    {
        $response = new Response();
        $response->getHeaders()->add('Foo', 'bar');
        $this->assertEquals("HTTP/1.1 200 OK\r\nFoo: bar\r\n\r\n", (string)$response);
    }

    public function testResponseWithNoHeadersOrBodyEndsWithBlankLine(): void
    {
        $response = new Response();
        $this->assertEquals("HTTP/1.1 200 OK\r\n\r\n", (string)$response);
    }
}
