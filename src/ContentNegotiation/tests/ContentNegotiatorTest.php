<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests;

use Aphiria\ContentNegotiation\ContentNegotiator;
use Aphiria\ContentNegotiation\ILanguageMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\ContentNegotiation\Tests\Mocks\User;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IRequest;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ContentNegotiatorTest extends TestCase
{
    private Headers $headers;
    private IRequest&MockObject $request;

    protected function setUp(): void
    {
        $this->headers = new Headers();
        $this->request = $this->createMock(IRequest::class);
        $this->request->method('getHeaders')
            ->willReturn($this->headers);
    }

    public function testEmptyListOfFormattersThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('List of formatters cannot be empty');
        new ContentNegotiator([]);
    }

    public function testGettingAcceptableResponseMediaTypesDoesNotReturnDuplicates(): void
    {
        $formatter1 = $this->createMock(IMediaTypeFormatter::class);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with('foo')
            ->willReturn(true);
        $formatter1->expects($this->once())
            ->method('getSupportedMediaTypes')
            ->willReturn(['foo/bar']);
        $formatter2 = $this->createMock(IMediaTypeFormatter::class);
        $formatter2->expects($this->once())
            ->method('canWriteType')
            ->with('foo')
            ->willReturn(true);
        $formatter2->expects($this->once())
            ->method('getSupportedMediaTypes')
            ->willReturn(['foo/bar']);
        $contentNegotiator = new ContentNegotiator([$formatter1, $formatter2]);
        $this->assertEquals(['foo/bar'], $contentNegotiator->getAcceptableResponseMediaTypes('foo'));
    }

    public function testGettingAcceptableResponseMediaTypesReturnsFormattersMediaTypesThatCanWriteType(): void
    {
        $formatter1 = $this->createMock(IMediaTypeFormatter::class);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with('foo')
            ->willReturn(true);
        $formatter1->expects($this->once())
            ->method('getSupportedMediaTypes')
            ->willReturn(['foo/bar']);
        $formatter2 = $this->createMock(IMediaTypeFormatter::class);
        $formatter2->expects($this->once())
            ->method('canWriteType')
            ->with('foo')
            ->willReturn(false);
        $formatter3 = $this->createMock(IMediaTypeFormatter::class);
        $formatter3->expects($this->once())
            ->method('canWriteType')
            ->with('foo')
            ->willReturn(true);
        $formatter3->expects($this->once())
            ->method('getSupportedMediaTypes')
            ->willReturn(['baz/blah']);
        $contentNegotiator = new ContentNegotiator([$formatter1, $formatter2, $formatter3]);
        $this->assertEquals(['foo/bar', 'baz/blah'], $contentNegotiator->getAcceptableResponseMediaTypes('foo'));
    }

    public function testNoMatchingRequestFormatterReturnsResultWithAllNullProperties(): void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $formatter->expects($this->once())
            ->method('canReadType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Content-Type', 'text/html');
        $negotiator = new ContentNegotiator([$formatter]);
        $result = $negotiator->negotiateRequestContent(User::class, $this->request);
        $this->assertNull($result->formatter);
        $this->assertNull($result->mediaType);
        $this->assertNull($result->encoding);
        $this->assertNull($result->language);
    }

    public function testNoMatchingResponseFormatterReturnsResultWithAllNullProperties(): void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', 'application/json');
        $negotiator = new ContentNegotiator([$formatter]);
        $result = $negotiator->negotiateResponseContent(User::class, $this->request);
        $this->assertNull($result->formatter);
        $this->assertNull($result->mediaType);
        $this->assertNull($result->encoding);
        $this->assertNull($result->language);
    }

    public function testNotSpecifyingFormattersIsAcceptable(): void
    {
        new ContentNegotiator();
        // Dummy assertion
        $this->assertTrue(true);
    }

    public function testRequestFormatterIsNullWithNoContentTypeSpecified(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $negotiator = new ContentNegotiator([$formatter]);
        $result = $negotiator->negotiateRequestContent(User::class, $this->request);
        $this->assertNull($result->formatter);
        $this->assertSame('application/octet-stream', $result->mediaType);
        $this->assertNull($result->encoding);
    }

    public function testRequestResultEncodingIsSetFromContentTypeHeaderIfSet(): void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-16']);
        $formatter->expects($this->once())
            ->method('canReadType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Content-Type', 'text/html; charset=utf-16');
        $this->headers->add('Content-Language', 'en-US');
        $negotiator = new ContentNegotiator([$formatter]);
        $result = $negotiator->negotiateRequestContent(User::class, $this->request);
        $this->assertSame($formatter, $result->formatter);
        $this->assertSame('text/html', $result->mediaType);
        $this->assertSame('utf-16', $result->encoding);
        $this->assertSame('en-US', $result->language);
    }

    public function testRequestResultLanguageIsSetFromContentLanguageHeaderIfSet(): void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $formatter->expects($this->once())
            ->method('canReadType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Content-Type', 'text/html; charset=utf-8');
        $this->headers->add('Content-Language', 'en-US');
        $negotiator = new ContentNegotiator([$formatter]);
        $result = $negotiator->negotiateRequestContent(User::class, $this->request);
        $this->assertSame($formatter, $result->formatter);
        $this->assertSame('text/html', $result->mediaType);
        $this->assertSame('utf-8', $result->encoding);
        $this->assertSame('en-US', $result->language);
    }

    public function testResponseEncodingIsSetFromAcceptCharsetHeaderIfSetAndAcceptHeaderIsNotSet(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-16']);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter->expects($this->once())
            ->method('getDefaultMediaType')
            ->willReturn('application/json');
        $this->headers->add('Accept-Charset', 'utf-16');
        $this->headers->add('Accept-Language', 'en-US');
        $negotiator = new ContentNegotiator([$formatter]);
        $result = $negotiator->negotiateResponseContent(User::class, $this->request);
        $this->assertSame($formatter, $result->formatter);
        $this->assertSame('application/json', $result->mediaType);
        $this->assertSame('utf-16', $result->encoding);
    }

    public function testResponseEncodingIsSetFromAcceptCharsetHeaderWhenPresent(): void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', 'application/json');
        $this->headers->add('Accept-Charset', 'utf-8');
        $negotiator = new ContentNegotiator([$formatter]);
        $result = $negotiator->negotiateResponseContent(User::class, $this->request);
        $this->assertSame($formatter, $result->formatter);
        $this->assertSame('utf-8', $result->encoding);
    }

    public function testResponseFormatterIsFirstFormatterRegisteredWithNoAcceptSpecified(): void
    {
        $formatter1 = $this->createMock(IMediaTypeFormatter::class);
        $formatter1->expects($this->once())
            ->method('getDefaultMediaType')
            ->willReturn('application/json');
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter2 = $this->createMock(IMediaTypeFormatter::class);
        $negotiator = new ContentNegotiator([$formatter1, $formatter2]);
        $result = $negotiator->negotiateResponseContent(User::class, $this->request);
        $this->assertSame($formatter1, $result->formatter);
        // Verify it's using the default media type
        $this->assertSame('application/json', $result->mediaType);
        $this->assertNull($result->encoding);
    }

    public function testResponseFormatterIsFirstFormatterThatCanWriteTypeWithNoAcceptSpecified(): void
    {
        $formatter1 = $this->createMock(IMediaTypeFormatter::class);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(false);
        $formatter2 = $this->createMock(IMediaTypeFormatter::class);
        $formatter2->expects($this->once())
            ->method('getDefaultMediaType')
            ->willReturn('application/json');
        $formatter2->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $negotiator = new ContentNegotiator([$formatter1, $formatter2]);
        $result = $negotiator->negotiateResponseContent(User::class, $this->request);
        $this->assertSame($formatter2, $result->formatter);
        // Verify it's using the default media type
        $this->assertSame('application/json', $result->mediaType);
        $this->assertNull($result->encoding);
    }

    public function testResponseFormatterIsNullWhenFirstFormatterRegisteredCannotWriteType(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(false);
        $negotiator = new ContentNegotiator([$formatter]);
        $result = $negotiator->negotiateResponseContent(User::class, $this->request);
        $this->assertNull($result->formatter);
        $this->assertNull($result->mediaType);
        $this->assertNull($result->encoding);
        $this->assertNull($result->language);
    }

    public function testResponseLanguageIsNullWhenNoMatchingSupportedLanguage(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $languageMatcher = $this->createMock(ILanguageMatcher::class);
        $languageMatcher->expects($this->once())
            ->method('getBestLanguageMatch')
            ->with($this->request)
            ->willReturn(null);
        $negotiator = new ContentNegotiator([$formatter], languageMatcher: $languageMatcher);
        $result = $negotiator->negotiateResponseContent(User::class, $this->request);
        $this->assertSame($formatter, $result->formatter);
        $this->assertNull($result->language);
    }

    public function testResponseLanguageIsSetFromLanguageMatcherResults(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $formatter->expects($this->once())
            ->method('getDefaultMediaType')
            ->willReturn('application/json');
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $languageMatcher = $this->createMock(ILanguageMatcher::class);
        $languageMatcher->expects($this->once())
            ->method('getBestLanguageMatch')
            ->with($this->request)
            ->willReturn('en-US');
        $this->headers->add('Accept-Charset', 'utf-8');
        $this->headers->add('Accept-Language', 'en-US');
        $negotiator = new ContentNegotiator([$formatter], languageMatcher: $languageMatcher);
        $result = $negotiator->negotiateResponseContent(User::class, $this->request);
        $this->assertSame($formatter, $result->formatter);
        $this->assertSame('application/json', $result->mediaType);
        $this->assertSame('utf-8', $result->encoding);
        $this->assertSame('en-US', $result->language);
    }

    /**
     * Creates a mock media type formatter with a list of supported media types
     *
     * @param list<string> $supportedMediaTypes The list of supported media types
     * @param int $numTimesSupportedMediaTypesCalled The number of times the formatter's supported media types will be checked
     * @return IMediaTypeFormatter&MockObject The mocked formatter
     */
    private function createFormatterMock(
        array $supportedMediaTypes,
        int $numTimesSupportedMediaTypesCalled
    ): IMediaTypeFormatter&MockObject {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->exactly($numTimesSupportedMediaTypesCalled))
            ->method('getSupportedMediaTypes')
            ->willReturn($supportedMediaTypes);

        return $formatter;
    }
}
