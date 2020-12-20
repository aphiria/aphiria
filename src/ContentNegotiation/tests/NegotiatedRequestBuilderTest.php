<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests;

use Aphiria\ContentNegotiation\IMediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatterMatch;
use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\ContentNegotiation\NegotiatedRequestBuilder;
use Aphiria\IO\Streams\IStream;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use Aphiria\Net\Http\IBody;
use Aphiria\Net\Http\IRequest;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class NegotiatedRequestBuilderTest extends TestCase
{
    private NegotiatedRequestBuilder $requestBuilder;

    protected function setUp(): void
    {
        $this->requestBuilder = new NegotiatedRequestBuilder();
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

    public function testBuildDefaultsAcceptHeader(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->build();
        $this->assertEquals(['*/*'], $request->getHeaders()->get('Accept'));
    }

    public function testWithBodyWithInvalidBodyTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Body must either implement ' . IBody::class . ' or be an array, object, or scalar');
        $this->requestBuilder->withBody(\fopen('php://temp', 'r+b'));
    }

    public function testWithBodyWithBodyInstanceSetsBodyToThatInstance(): void
    {
        $expectedBody = $this->createMock(IBody::class);
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withBody($expectedBody)
            ->build();
        $this->assertSame($expectedBody, $request->getBody());
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
        (new NegotiatedRequestBuilder($mediaTypeFormatterMatcher))->withBody('foo');
    }

    /**
     * @dataProvider getRawBodies
     * @param string $expectedType The expected type
     * @param mixed $rawBody The raw body
     */
    public function testWithBodyWithNonHttpBodyUsesContentNegotiationToSetBody(string $expectedType, mixed $rawBody): void
    {
        $mediaTypeFormatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeFormatter->method('getDefaultEncoding')
            ->willReturn('UTF-8');
        $expectedStream = null;
        $mediaTypeFormatter->method('writeToStream')
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
        $request = (new NegotiatedRequestBuilder($mediaTypeFormatterMatcher))->withMethod('GET')
            ->withUri('http://localhost')
            ->withBody($rawBody)
            ->build();
        $this->assertSame($expectedStream, $request->getBody()?->readAsStream());
        $this->assertSame('application/json', $request->getHeaders()->getFirst('Content-Type'));
    }

    public function testWithBodyWithNullBodySetsBodyToNull(): void
    {
        $request = $this->requestBuilder->withMethod('GET')
            ->withUri('http://localhost')
            ->withBody(null)
            ->build();
        $this->assertNull($request->getBody());
    }
}
