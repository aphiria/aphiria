<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use InvalidArgumentException;
use Opulence\Net\Http\Formatting\ContentNegotiator;
use Opulence\Net\Http\Formatting\IMediaTypeFormatter;
use Opulence\Net\Http\HttpHeaders;
use Opulence\Net\Http\IHttpRequestMessage;

/**
 * Tests the content negotiator
 */
class ContentNegotiatorTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContentNegotiator The content negotiator to use in tests */
    private $negotiator;
    /** @var IHttpRequestMessage|\PHPUnit_Framework_MockObject_MockObject The request message to use in tests */
    private $request;
    /** @var HttpHeaders The headers to use in tests */
    private $headers;

    /**
     * Sets up the tests
     */
    public function setUp(): void
    {
        $this->negotiator = new ContentNegotiator();
        $this->headers = new HttpHeaders();
        $this->request = $this->createMock(IHttpRequestMessage::class);
        $this->request->expects($this->any())
            ->method('getHeaders')
            ->willReturn($this->headers);
    }

    /**
     * Tests that an empty list of formatters throws an exception when negotiating the request
     */
    public function testEmptyListOfFormattersThrowsExceptionWhenNegotiatingRequest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->negotiator->negotiateRequestContent($this->request, []);
    }

    /**
     * Tests that an empty list of formatters throws an exception when negotiating the response
     */
    public function testEmptyListOfFormattersThrowsExceptionWhenNegotiatingResponse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->negotiator->negotiateResponseContent($this->request, [], []);
    }

    /**
     * Tests that no matching request formatter returns null
     */
    public function testNoMatchingRequestFormatterReturnsNull(): void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $this->headers->add('Content-Type', 'text/html');
        $result = $this->negotiator->negotiateRequestContent($this->request, [$formatter], []);
        $this->assertNull($result);
    }

    /**
     * Tests that no matching response formatter returns null
     */
    public function testNoMatchingResponseFormatterReturnsNull(): void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $this->headers->add('Accept', 'application/json');
        $this->assertNull($this->negotiator->negotiateResponseContent($this->request, [$formatter], []));
    }

    /**
     * Tests that the request result's encoding is set from the Content-Type header if set
     */
    public function testRequestResultEncodingIsSetFromContentTypeHeaderIfSet(): void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-16']);
        $this->headers->add('Content-Type', 'text/html; charset=utf-16');
        $this->headers->add('Content-Language', 'en-US');
        $result = $this->negotiator->negotiateRequestContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('text/html', $result->getMediaType());
        $this->assertEquals('utf-16', $result->getEncoding());
        $this->assertEquals('en-US', $result->getLanguage());
    }

    /**
     * Tests that matching the request formatter when no content-type is specified returns the first registered formatter
     */
    public function testRequestFormatterIsFirstFormatterRegisteredWithNoContentTypeSpecified(): void
    {
        $formatter1 = $this->createMock(IMediaTypeFormatter::class);
        $formatter2 = $this->createMock(IMediaTypeFormatter::class);
        $result = $this->negotiator->negotiateRequestContent($this->request, [$formatter1, $formatter2]);
        $this->assertSame($formatter1, $result->getFormatter());
        $this->assertEquals('application/octet-stream', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that the request result's laguage is set from the Content-Language header if set
     */
    public function testRequestResultLanguageIsSetFromContentLanguageHeaderIfSet(): void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $this->headers->add('Content-Type', 'text/html; charset=utf-8');
        $this->headers->add('Content-Language', 'en-US');
        $result = $this->negotiator->negotiateRequestContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('text/html', $result->getMediaType());
        $this->assertEquals('utf-8', $result->getEncoding());
        $this->assertEquals('en-US', $result->getLanguage());
    }

    /**
     * Tests that matching the response formatter when no Accept is specified returns the first registered formatter
     */
    public function testResponseFormatterIsFirstFormatterRegisteredWithNoAcceptSpecified(): void
    {
        $formatter1 = $this->createMock(IMediaTypeFormatter::class);
        $formatter2 = $this->createMock(IMediaTypeFormatter::class);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter1, $formatter2], []);
        $this->assertSame($formatter1, $result->getFormatter());
        $this->assertEquals('application/octet-stream', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that the response encoding is set from the Accept-Charset header if set and the Accept header isn't
     */
    public function testResponseEncodingIsSetFromAcceptCharsetHeaderIfSetAndAcceptHeaderIsNotSet(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-16']);
        $this->headers->add('Accept-Charset', 'utf-16');
        $this->headers->add('Accept-Language', 'en-US');
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter], ['en-US']);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('application/octet-stream', $result->getMediaType());
        $this->assertEquals('utf-16', $result->getEncoding());
        $this->assertEquals('en-US', $result->getLanguage());
    }

    /**
     * Tests that the response language is null when there's no matching supported language
     */
    public function testResponseLanguageIsNullWhenNoMatchingSupportedLanguage(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $this->headers->add('Accept-Charset', 'utf-8');
        $this->headers->add('Accept-Language', 'en-US');
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter], ['en-GB']);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertNull($result->getLanguage());
    }

    /**
     * Tests that the response language is set from the Accept-Language header
     */
    public function testResponseLanguageIsSetFromAcceptLanguageHeader(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $this->headers->add('Accept-Charset', 'utf-8');
        $this->headers->add('Accept-Language', 'en-US');
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter], ['en-US']);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('application/octet-stream', $result->getMediaType());
        $this->assertEquals('utf-8', $result->getEncoding());
        $this->assertEquals('en-US', $result->getLanguage());
    }

    /**
     * Creates a mock media type formatter with a list of supported media types
     *
     * @param array $supportedMediaTypes The list of supported media types
     * @param int $numTimesSupportedMediaTypesCalled The number of times the formatter's supported media types will be checked
     * @return IMediaTypeFormatter|\PHPUnit_Framework_MockObject_MockObject The mocked formatter
     */
    private function createFormatterMock(
        array $supportedMediaTypes,
        int $numTimesSupportedMediaTypesCalled
    ) : IMediaTypeFormatter {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->exactly($numTimesSupportedMediaTypesCalled))
            ->method('getSupportedMediaTypes')
            ->willReturn($supportedMediaTypes);

        return $formatter;
    }
}
