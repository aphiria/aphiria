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

use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\RequestBuilder;
use Aphiria\Net\Http\RequestTargetType;
use Aphiria\Net\Uri;
use LogicException;
use PHPUnit\Framework\TestCase;

class RequestBuilderTest extends TestCase
{
    private RequestBuilder $requestBuilder;

    protected function setUp(): void
    {
        $this->requestBuilder = new RequestBuilder();
    }

    public function testBuildWithoutSettingMethodThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Method is not set');
        $this->requestBuilder->withUri('http://localhost')
            ->build();
    }

    public function testBuildWithoutSettingUriThrowsException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('URI is not set');
        $this->requestBuilder->withMethod('GET')
            ->build();
    }

    public function testRequestDefaultsTo1Point1ProtocolVersion(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->build();
        $this->assertSame('1.1', $request->getProtocolVersion());
    }

    public function testRequestDefaultsToOriginFormRequestTargetType(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost/path')
            ->build();
        $this->assertSame('GET /path HTTP/1.1', \explode("\r\n", (string)$request)[0]);
        $this->assertSame('localhost', $request->getHeaders()->getFirst('Host'));
    }

    public function testWithBodyWithHttpBodyUsesThatBody(): void
    {
        $body = $this->createMock(IBody::class);
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withBody($body)
            ->build();
        $this->assertSame($body, $request->getBody());
    }

    public function testWithBodyWithNullBodySetsBodyToNull(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withBody(null)
            ->build();
        $this->assertNull($request->getBody());
    }

    public function testWithHeaderCanAppendToHeader(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withHeader('Foo', 'bar')
            ->withHeader('Foo', 'baz', true)
            ->build();
        $this->assertEquals(['bar', 'baz'], $request->getHeaders()->get('Foo'));
    }

    public function testWithHeaderSetsHeader(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withHeader('Foo', 'bar')
            ->build();
        $this->assertEquals(['bar'], $request->getHeaders()->get('Foo'));
    }

    public function testWithManyHeadersSetsHeaders(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withManyHeaders(['Foo' => 'bar', 'Baz' => ['blah', 'dave']])
            ->build();
        $this->assertEquals(['bar'], $request->getHeaders()->get('Foo'));
        $this->assertEquals(['blah', 'dave'], $request->getHeaders()->get('Baz'));
    }

    public function testWithMethodSetsMethod(): void
    {
        $request = $this->requestBuilder->withMethod('POST')
            ->withUri('http://localhost')
            ->build();
        $this->assertSame('POST', $request->getMethod());
    }

    public function testWithPropertyAddsProperty(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withProperty('foo', 'bar')
            ->build();
        $this->assertSame('bar', $request->getProperties()->get('foo'));
    }

    public function testWithProtocolVersionSetsProtocolVersion(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withProtocolVersion('2.0')
            ->build();
        $this->assertSame('GET / HTTP/2.0', \explode("\r\n", (string)$request)[0]);
    }

    public function testWithRequestTargetTypeSetsRequestTargetType(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withRequestTargetType(RequestTargetType::AbsoluteForm)
            ->build();
        $this->assertSame('GET http://localhost HTTP/1.1', \explode("\r\n", (string)$request)[0]);
    }

    public function testWithStringUriSetsRequestUri(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->build();
        $this->assertSame('http://localhost', (string)$request->getUri());
    }

    public function testWithUriSetsRequestUri(): void
    {
        $uri = new Uri('http://localhost');
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri($uri)
            ->build();
        $this->assertSame($uri, $request->getUri());
    }
}
