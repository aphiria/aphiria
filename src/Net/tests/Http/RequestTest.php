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

use Aphiria\Collections\HashTable;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Http\RequestTargetTypes;
use Aphiria\Net\Http\StringBody;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    private Request $request;
    private Headers $headers;
    private IBody|MockObject $body;
    private Uri $uri;
    private HashTable $properties;

    protected function setUp(): void
    {
        $this->headers = new Headers();
        $this->body = $this->createMock(IBody::class);
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
        $this->assertSame('2.0', $this->request->getProtocolVersion());
    }

    public function testGettingUri(): void
    {
        $this->assertSame($this->uri, $this->request->getUri());
    }

    public function testHostHeaderIsNotAddedIfItAlreadyExists(): void
    {
        $headers = new Headers();
        $headers->add('Host', 'foo.com');
        $request = new Request('GET', new Uri('https://bar.com'), $headers);
        $this->assertSame('foo.com', $request->getHeaders()->getFirst('Host'));
    }

    public function testInvalidRequestTargetTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Request target type foo is invalid');
        new Request('GET', new Uri('https://example.com'), null, null, null, '1.1', 'foo');
    }

    public function testMultipleHeaderValuesAreConcatenatedWithCommas(): void
    {
        $request = new Request('GET', new Uri('https://example.com'));
        $request->getHeaders()->add('Foo', 'bar');
        $request->getHeaders()->add('Foo', 'baz', true);
        $this->assertSame("GET / HTTP/1.1\r\nHost: example.com\r\nFoo: bar, baz\r\n\r\n", (string)$request);
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
        $this->assertSame(
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
        $this->assertSame("GET * HTTP/1.1\r\nHost: example.com\r\n\r\n", (string)$request);
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
        $this->assertSame("GET www.example.com:4343 HTTP/1.1\r\n\r\n", (string)$request);
    }

    public function requestTargetQueryStringProvider(): array
    {
        return [
            ['GET', 'https://example.com/foo', "GET /foo HTTP/1.1\r\nHost: example.com\r\n\r\n"],
            ['GET', 'https://example.com/foo?bar', "GET /foo?bar HTTP/1.1\r\nHost: example.com\r\n\r\n"],
            ['GET', 'https://example.com:8080/foo?bar', "GET /foo?bar HTTP/1.1\r\nHost: example.com:8080\r\n\r\n"],
        ];
    }

    /**
     * @dataProvider requestTargetQueryStringProvider
     * @param string $requestQueryString The request query string
     * @param string $uri The request URI
     * @param string $expectedRequestQueryString The expected request query string
     */
    public function testRequestTargetTypeOriginFormIncludesHostHeader(string $requestQueryString, string $uri, string $expectedRequestQueryString): void
    {
        $requestQueryString = new Request($requestQueryString, new Uri($uri));
        $this->assertEquals($expectedRequestQueryString, (string)$requestQueryString);
    }

    public function testRequestWithHeadersAndBodyEndsWithBody(): void
    {
        $request = new Request('GET', new Uri('https://example.com'), new Headers(), new StringBody('foo'));
        $request->getHeaders()->add('Foo', 'bar');
        $this->assertSame("GET / HTTP/1.1\r\nHost: example.com\r\nFoo: bar\r\n\r\nfoo", (string)$request);
    }

    public function testRequestWithHeadersButNoBodyEndsWithBlankLine(): void
    {
        $request = new Request('GET', new Uri('https://example.com'));
        $request->getHeaders()->add('Foo', 'bar');
        $this->assertSame("GET / HTTP/1.1\r\nHost: example.com\r\nFoo: bar\r\n\r\n", (string)$request);
    }

    public function testRequestWithNoHeadersOrBodyEndsWithBlankLine(): void
    {
        $request = new Request('GET', new Uri('https://example.com'));
        $this->assertSame("GET / HTTP/1.1\r\nHost: example.com\r\n\r\n", (string)$request);
    }

    public function testSettingBody(): void
    {
        /** @var IBody $body */
        $body = $this->createMock(IBody::class);
        $this->request->setBody($body);
        $this->assertSame($body, $this->request->getBody());
    }

    public function testSettingInvalidMethodThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HTTP method FOO');
        new Request('foo', $this->uri, $this->headers, $this->body);
    }
}
