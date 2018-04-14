<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http;

use InvalidArgumentException;
use Opulence\Collections\HashTable;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpBody;
use Opulence\Net\Http\Request;
use Opulence\Net\Http\RequestTargetTypes;
use Opulence\Net\Uri;

/**
 * Tests the request
 */
class RequestTest extends \PHPUnit\Framework\TestCase
{
    /** @var Request The request to use in tests */
    private $request;
    /** @var HttpHeaders The headers */
    private $headers;
    /** @var IHttpBody|\PHPUnit_Framework_MockObject_MockObject The mock body */
    private $body;
    /** @var Uri The request URI */
    private $uri;
    /** @var HashTable The request properties */
    private $properties;

    public function setUp(): void
    {
        $this->headers = new HttpHeaders();
        $this->body = $this->createMock(IHttpBody::class);
        $this->uri = new Uri('https://example.com');
        $this->properties = new HashTable([new KeyValuePair('foo', 'bar')]);
        $this->request = new Request(
            'GET',
            $this->uri,
            $this->headers,
            $this->body,
            $this->properties,
            '2.0'
        );
    }

    public function testGettingBody(): void
    {
        $this->assertSame($this->body, $this->request->getBody());
    }

    public function testGettingHeaders(): void
    {
        $this->assertSame($this->headers, $this->request->getHeaders());
    }

    public function testGettingMethod(): void
    {
        $this->assertSame('GET', $this->request->getMethod());
    }

    public function testGettingProperties(): void
    {
        $this->assertSame($this->properties, $this->request->getProperties());
    }

    public function testGettingProtocolVersion(): void
    {
        $this->assertEquals('2.0', $this->request->getProtocolVersion());
    }

    public function testGettingUri(): void
    {
        $this->assertSame($this->uri, $this->request->getUri());
    }

    public function testHostHeaderIsNotAddedIfItAlreadyExists(): void
    {
        $headers = new HttpHeaders();
        $headers->add('Host', 'foo.com');
        $request = new Request('GET', new Uri('https://bar.com'), $headers);
        $this->assertEquals('foo.com', $request->getHeaders()->getFirst('Host'));
    }

    public function testInvalidRequestTargetTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Request('GET', new Uri('https://example.com'), null, null, null, '1.1', 'foo');
    }

    public function testMultipleHeaderValuesAreConcatenatedWithCommas(): void
    {
        $request = new Request('GET', new Uri('https://example.com'));
        $request->getHeaders()->add('Foo', 'bar');
        $request->getHeaders()->add('Foo', 'baz', true);
        $this->assertEquals("GET / HTTP/1.1\r\nHost: example.com\r\nFoo: bar, baz\r\n\r\n", (string)$request);
    }

    public function testRequestTargetTypeAbsoluteFormIncludesEntireUri(): void
    {
        $request = new Request(
            'GET',
            new Uri('https://example.com:4343/foo?bar'),
            null,
            null,
            null,
            '1.1',
            RequestTargetTypes::ABSOLUTE_FORM
        );
        $this->assertEquals(
            "GET https://example.com:4343/foo?bar HTTP/1.1\r\nHost: example.com:4343\r\n\r\n",
            (string)$request
        );
    }

    public function testRequestTargetTypeAsteriskFormUsesAsteriskForRequestTarget(): void
    {
        $request = new Request(
            'GET',
            new Uri('https://example.com'),
            null,
            null,
            null,
            '1.1',
            RequestTargetTypes::ASTERISK_FORM
        );
        $this->assertEquals("GET * HTTP/1.1\r\nHost: example.com\r\n\r\n", (string)$request);
    }

    public function testRequestTargetTypeAuthorityFormIncludeUriAuthorityWithoutUserInfo(): void
    {
        $request = new Request(
            'GET',
            new Uri('https://user:password@www.example.com:4343/foo?bar'),
            null,
            null,
            null,
            '1.1',
            RequestTargetTypes::AUTHORITY_FORM
        );
        $this->assertEquals("GET www.example.com:4343 HTTP/1.1\r\n\r\n", (string)$request);
    }

    public function testRequestTargetTypeOriginFormIncludesHostHeader(): void
    {
        $requestWithUriWithoutQueryString = new Request('GET', new Uri('https://example.com/foo'));
        $this->assertEquals(
            "GET /foo HTTP/1.1\r\nHost: example.com\r\n\r\n",
            (string)$requestWithUriWithoutQueryString
        );
        $requestWithUriWithoutPort = new Request('GET', new Uri('https://example.com/foo?bar'));
        $this->assertEquals("GET /foo?bar HTTP/1.1\r\nHost: example.com\r\n\r\n", (string)$requestWithUriWithoutPort);
        $requestWithUriWithPort = new Request('GET', new Uri('https://example.com:8080/foo?bar'));
        $this->assertEquals("GET /foo?bar HTTP/1.1\r\nHost: example.com:8080\r\n\r\n", (string)$requestWithUriWithPort);
    }

    public function testRequestWithHeadersButNoBodyEndsWithBlankLine(): void
    {
        $request = new Request('GET', new Uri('https://example.com'));
        $request->getHeaders()->add('Foo', 'bar');
        $this->assertEquals("GET / HTTP/1.1\r\nHost: example.com\r\nFoo: bar\r\n\r\n", (string)$request);
    }

    public function testRequestWithNoHeadersOrBodyEndsWithBlankLine(): void
    {
        $request = new Request('GET', new Uri('https://example.com'));
        $this->assertEquals("GET / HTTP/1.1\r\nHost: example.com\r\n\r\n", (string)$request);
    }

    public function testSettingBody(): void
    {
        /** @var IHttpBody $body */
        $body = $this->createMock(IHttpBody::class);
        $this->request->setBody($body);
        $this->assertSame($body, $this->request->getBody());
    }

    public function testSettingInvalidMethodThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Request('foo', $this->uri, $this->headers, $this->body);
    }
}
