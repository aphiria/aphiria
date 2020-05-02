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

use Aphiria\IO\Streams\IStream;
use Aphiria\Net\Http\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatterMatch;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\RequestBuilder;
use Aphiria\Net\Http\RequestTargetTypes;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;

class RequestBuilderTest extends TestCase
{
    private RequestBuilder $requestBuilder;

    protected function setUp(): void
    {
        $this->requestBuilder = new RequestBuilder();
    }

    public function getRawBodies(): array
    {
        return [
            ['string[]', ['foo', 'bar']],
            ['string', 'foo'],
            [self::class, $this],
            [self::class . '[]', [$this, $this]]
        ];
    }

    public function testBuildDefaultsMediaTypeHeaders(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->build();
        $this->assertEquals(['application/json'], $request->getHeaders()->get('Content-Type'));
        $this->assertEquals(['*/*'], $request->getHeaders()->get('Accept'));
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

    public function testDefaultMediaTypeHeadersAreOverridable(): void
    {
        $request = (new RequestBuilder(null, 'application/json', 'text/plain'))->withMethod('GET')
            ->withUri('http://localhost')
            ->build();
        $this->assertEquals(['application/json'], $request->getHeaders()->get('Content-Type'));
        $this->assertEquals(['text/plain'], $request->getHeaders()->get('Accept'));
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
        $this->assertEquals('GET /path HTTP/1.1', explode("\r\n", (string)$request)[0]);
        $this->assertEquals('localhost', $request->getHeaders()->getFirst('Host'));
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

    public function testWithBodyWithInvalidBodyTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Body must either implement ' . IBody::class . ' or be an array, object, or scalar');
        $this->requestBuilder->withBody(\fopen('php://temp', 'r+b'));
    }

    public function testWithBodyWithNonHttpBodyThatCannotBeNegotiatedThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No media type formatter available for string');
        $mediaTypeFormatterMatcher = $this->createMock(IMediaTypeFormatterMatcher::class);
        $mediaTypeFormatterMatcher->expects($this->once())
            ->method('getBestRequestMediaTypeFormatterMatch')
            ->with('string', $this->isInstanceOf(IRequest::class))
            ->willReturn(null);
        (new RequestBuilder($mediaTypeFormatterMatcher))->withBody('foo');
    }

    /**
     * @dataProvider getRawBodies
     * @param string $expectedType The expected type
     * @param mixed $rawBody The raw body
     */
    public function testWithBodyWithNonHttpBodyUsesContentNegotiationToSetBody(string $expectedType, $rawBody): void
    {
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->expects($this->at(0))
            ->method('getDefaultEncoding')
            ->willReturn('UTF-8');
        $expectedStream = null;
        $mediaTypeFormatter->expects($this->at(1))
            ->method('writeToStream')
            ->with(
                $rawBody,
                $this->callback(function (IStream $stream) use (&$expectedStream) {
                    $expectedStream = $stream;

                    return true;
                }),
                'UTF-8'
            );
        $expectedMediaTypeFormatterMatch = new MediaTypeFormatterMatch(
            $mediaTypeFormatter,
            'application/json',
            new ContentTypeHeaderValue('application/json')
        );
        $mediaTypeFormatterMatcher = $this->createMock(IMediaTypeFormatterMatcher::class);
        $mediaTypeFormatterMatcher->expects($this->once())
            ->method('getBestRequestMediaTypeFormatterMatch')
            ->with($expectedType, $this->isInstanceOf(IRequest::class))
            ->willReturn($expectedMediaTypeFormatterMatch);
        $request = (new RequestBuilder($mediaTypeFormatterMatcher))->withMethod('GET')
            ->withUri('http://localhost')
            ->withBody($rawBody)
            ->build();
        $this->assertSame($expectedStream, $request->getBody()->readAsStream());
        $this->assertEquals('application/json', $request->getHeaders()->getFirst('Content-Type'));
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
        $this->assertEquals('bar', $request->getProperties()->get('foo'));
    }

    public function testWithProtocolVersionSetsProtocolVersion(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withProtocolVersion('2.0')
            ->build();
        $this->assertEquals('GET / HTTP/2.0', explode("\r\n", (string)$request)[0]);
    }

    public function testWithRequestTargetTypeSetsRequestTargetType(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withRequestTargetType(RequestTargetTypes::ABSOLUTE_FORM)
            ->build();
        $this->assertEquals('GET http://localhost HTTP/1.1', explode("\r\n", (string)$request)[0]);
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

    public function testWithUriWithInvalidUriTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('URI must be instance of ' . Uri::class . ' or string');
        $this->requestBuilder->withUri([]);
    }
}
