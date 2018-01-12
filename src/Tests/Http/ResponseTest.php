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
use Opulence\Net\Http\HttpStatusCodes;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\Response;

/**
 * Tests the response class
 */
class ResponseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that the default reason phrase is set
     */
    public function testDefaultReasonPhraseIsSet() : void
    {
        $response = new Response(200);
        $this->assertEquals(HttpStatusCodes::getDefaultReasonPhrase(200), $response->getReasonPhrase());
    }

    /**
     * Tests getting and setting the body
     */
    public function testGettingAndSettingBody() : void
    {
        $body1 = $this->createMock(IHttpBody::class);
        $response = new Response(200, null, $body1);
        $this->assertSame($body1, $response->getBody());
        $body2 = $this->createMock(IHttpBody::class);
        $response->setBody($body2);
        $this->assertSame($body2, $response->getBody());
    }

    /**
     * Tests getting and setting the status code
     */
    public function testGettingAndSettingStatusCode() : void
    {
        $response = new Response(201);
        $this->assertEquals(201, $response->getStatusCode());
        $response->setStatusCode(202);
        $this->assertEquals(202, $response->getStatusCode());
    }

    /**
     * Tests getting headers
     */
    public function testGettingHeaders() : void
    {
        $headers = new HttpHeaders();
        $response = new Response(200, $headers);
        $this->assertSame($headers, $response->getHeaders());
    }

    /**
     * Tests getting the protocol version
     */
    public function testGettingProtocolVersion() : void
    {
        $response = new Response(200, null, null, '2.0');
        $this->assertEquals('2.0', $response->getProtocolVersion());
    }

    /**
     * Tests that multiple header values are concatenated with commas
     */
    public function testMultipleHeaderValuesAreConcatenatedWithCommas() : void
    {
        $response = new Response();
        $response->getHeaders()->add('Foo', 'bar');
        $response->getHeaders()->add('Foo', 'baz', true);
        $this->assertEquals("HTTP/1.1 200 OK\r\nFoo: bar, baz\r\n\r\n", (string)$response);
    }

    /**
     * Tests that the reason phrase is included only if it is defined
     */
    public function testReasonPhraseIsIncludedOnlyIfDefined() : void
    {
        $response = new Response();
        $response->setStatusCode(200, 'OK');
        $this->assertEquals("HTTP/1.1 200 OK\r\n\r\n", (string)$response);
    }

    /**
     * Tests that a response with headers but no body ends with a blank line
     */
    public function testResponseWithHeadersButNoBodyEndsWithBlankLine() : void
    {
        $response = new Response();
        $response->getHeaders()->add('Foo', 'bar');
        $this->assertEquals("HTTP/1.1 200 OK\r\nFoo: bar\r\n\r\n", (string)$response);
    }

    /**
     * Tests that a response with no headers or body ends with a blank line
     */
    public function testResponseWithNoHeadersOrBodyEndsWithBlankLine() : void
    {
        $response = new Response();
        $this->assertEquals("HTTP/1.1 200 OK\r\n\r\n", (string)$response);
    }
}
