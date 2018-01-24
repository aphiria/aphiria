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
    public function setUp() : void
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
    public function testEmptyListOfFormattersThrowsExceptionWhenNegotiatingRequest() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->negotiator->negotiateRequestContent($this->request, []);
    }

    /**
     * Tests that an empty list of formatters throws an exception when negotiating the response
     */
    public function testEmptyListOfFormattersThrowsExceptionWhenNegotiatingResponse() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->negotiator->negotiateResponseContent($this->request, []);
    }

    /**
     * Tests that the matcher selects the read formatter that supports its content type
     */
    public function testNegotiatorSelectsReadFormatterThatSupportsContentType() : void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter2 = $this->createFormatterMock(['text/html'], 1);
        $this->headers->add('Content-Type', 'text/html');
        $result = $this->negotiator->negotiateRequestContent($this->request, [$formatter1, $formatter2]);
        $this->assertSame($formatter2, $result->getFormatter());
        $this->assertEquals('text/html', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that matching a write formatter with an invalid media type throws an exception
     */
    public function testNegotiatingWriteFormatterWithInvalidMediaTypeThrowsException() : void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);

        try {
            $this->headers->add('Accept', 'text');
            $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
            $this->fail('"text" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $this->headers->add('Accept', 'text/');
            $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
            $this->fail('"text/" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $this->headers->add('Accept', '/html');
            $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
            $this->fail('"/html" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests that no matching request formatter returns null
     */
    public function testNoMatchingRequestFormatterReturnsNull() : void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $this->headers->add('Content-Type', 'text/html');
        $result = $this->negotiator->negotiateRequestContent($this->request, [$formatter]);
        $this->assertNull($result);
    }

    /**
     * Tests that no matching response formatter returns null
     */
    public function testNoMatchingResponseFormatterReturnsNull() : void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $this->headers->add('Accept', 'application/json');
        $this->assertNull($this->negotiator->negotiateResponseContent($this->request, [$formatter]));
    }

    /**
     * Tests that the request result's charset is null if the formatter does not support it
     */
    public function testRequestResultCharsetIsNullIfFormatterDoesNotSupportIt() : void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $this->headers->add('Content-Type', 'text/html; charset=utf-16');
        $result = $this->negotiator->negotiateRequestContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('text/html', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that the request result's charset is set from the Content-Type header if set
     */
    public function testRequestResultCharsetIsSetFromContentTypeHeaderIfSet() : void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-16']);
        $this->headers->add('Content-Type', 'text/html; charset=utf-16');
        $result = $this->negotiator->negotiateRequestContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('text/html', $result->getMediaType());
        $this->assertEquals('utf-16', $result->getEncoding());
    }

    /**
     * Tests that matching the request formatter when no content-type is specified returns the first registered formatter
     */
    public function testRequestFormatterIsFirstFormatterRegisteredWithNoContentTypeSpecified() : void
    {
        $formatter1 = $this->createMock(IMediaTypeFormatter::class);
        $formatter2 = $this->createMock(IMediaTypeFormatter::class);
        $result = $this->negotiator->negotiateRequestContent($this->request, [$formatter1, $formatter2]);
        $this->assertSame($formatter1, $result->getFormatter());
        $this->assertEquals('application/octet-stream', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that matching the response formatter when no Accept is specified returns the first registered formatter
     */
    public function testResponseFormatterIsFirstFormatterRegisteredWithNoAcceptSpecified() : void
    {
        $formatter1 = $this->createMock(IMediaTypeFormatter::class);
        $formatter2 = $this->createMock(IMediaTypeFormatter::class);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter1, $formatter2]);
        $this->assertSame($formatter1, $result->getFormatter());
        $this->assertEquals('application/octet-stream', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that a response formatter can match a wildcard sub-type
     */
    public function testResponseFormatterCanMatchWithWildcardSubType() : void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter2 = $this->createFormatterMock(['text/html'], 1);
        $this->headers->add('Accept', 'text/*', true);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter1, $formatter2]);
        $this->assertSame($formatter2, $result->getFormatter());
        $this->assertEquals('text/html', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that a response formatter can match a wildcard type
     */
    public function testResponseFormatterCanMatchWithWildcardType() : void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter2 = $this->createFormatterMock(['text/html'], 0);
        $this->headers->add('Accept', '*/*', true);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter1, $formatter2]);
        $this->assertSame($formatter1, $result->getFormatter());
        $this->assertEquals('application/json', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that a response formatter matches the most specific media type with equal quality media types
     */
    public function testResponseFormatterMatchesMostSpecificMediaTypeWithEqualQualityMediaTypes() : void
    {
        $formatter1 = $this->createFormatterMock(['text/plain'], 1);
        $formatter2 = $this->createFormatterMock(['text/xml'], 1);
        $formatter3 = $this->createFormatterMock(['text/html'], 1);
        $this->headers->add('Accept', '*/*', true);
        $this->headers->add('Accept', 'text/*', true);
        $this->headers->add('Accept', 'text/html', true);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter1, $formatter2, $formatter3]);
        $this->assertSame($formatter3, $result->getFormatter());
        $this->assertEquals('text/html', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that a response formatter can match a wildcard sub-type with a higher quality score than a specific media type
     */
    public function testResponseFormatterMatchesWildcardSubTypeWithHigherQualityScoreThanSpecificMediaType() : void
    {
        $formatter = $this->createFormatterMock(['text/plain', 'text/html'], 1);
        $this->headers->add('Accept', 'text/*; q=0.5', true);
        $this->headers->add('Accept', 'text/html; q=0.3', true);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('text/plain', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that a response formatter can match a wildcard type with a higher quality score than a specific media type
     */
    public function testResponseFormatterMatchesWildcardTypeWithHigherQualityScoreThanSpecificMediaType() : void
    {
        $formatter = $this->createFormatterMock(['application/json', 'text/html'], 1);
        $this->headers->add('Accept', '*/*; q=0.5', true);
        $this->headers->add('Accept', 'text/html; q=0.3', true);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('application/json', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that a response formatter that matches a zero quality media type returns a null match
     */
    public function testResponseFormatterThatMatchesZeroQualityMediaTypeReturnsNullMatch() : void
    {
        // The media type should be filtered out of the list of media types to check against
        $formatter = $this->createFormatterMock(['text/html'], 0);
        $this->headers->add('Accept', 'text/html; q=0.0');
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
        $this->assertNull($result);
    }

    /**
     * Tests that the response media type is set from the Accept-Charset header if set and the Accept header isn't
     */
    public function testResponseMediaTypeIsSetFromAcceptCharsetHeaderIfSetAndAcceptHeaderIsNotSet() : void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-16']);
        $this->headers->add('Accept-Charset', 'utf-16');
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('application/octet-stream', $result->getMediaType());
        $this->assertEquals('utf-16', $result->getEncoding());
    }

    /**
     * Tests that the response result's charset is null if the formatter does not support the charset in the Accept=Charset header
     */
    public function testResponseResultCharsetIsNullIfFormatterDoesNotSupportCharsetInAcceptCharsetHeader() : void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $this->headers->add('Accept', 'text/html');
        $this->headers->add('Accept-Charset', 'utf-16');
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('text/html', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that the response result's charset is null if the formatter does not support the charset in the Accept header
     */
    public function testResponseResultCharsetIsNullIfFormatterDoesNotSupportCharsetInAcceptHeader() : void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $this->headers->add('Accept', 'text/html; charset=utf-16');
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('text/html', $result->getMediaType());
        $this->assertNull($result->getEncoding());
    }

    /**
     * Tests that the response result gets the charset from the Accept-Charset header when present
     */
    public function testResponseResultGetsCharsetFromAcceptCharsetHeaderWhenPresent() : void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-16']);
        $this->headers->add('Accept', 'text/html', true);
        $this->headers->add('Accept-Charset', 'utf-16', true);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('text/html', $result->getMediaType());
        $this->assertEquals('utf-16', $result->getEncoding());
    }

    /**
     * Tests that the response result gets the charset from the Accept header when no Accept-Charset header is present
     */
    public function testResponseResultGetsCharsetFromAcceptHeaderWhenNoAcceptCharsetHeaderIsPresent() : void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-16']);
        $this->headers->add('Accept', 'text/html; charset=utf-16', true);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('text/html', $result->getMediaType());
        $this->assertEquals('utf-16', $result->getEncoding());
    }

    /**
     * Tests that supported encodings are chosen in order of their quality score
     */
    public function testSupportedEncodingsAreChosenInOrderOfQualityScore() : void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8', 'utf-16']);
        $this->headers->add('Accept', 'application/json', true);
        $this->headers->add('Accept-Charset', 'utf-8; q=0.1', true);
        $this->headers->add('Accept-Charset', 'utf-16; q=0.5', true);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('application/json', $result->getMediaType());
        $this->assertEquals('utf-16', $result->getEncoding());
    }

    /**
     * Tests that a wildcard encoding matches any formatter in a request result
     */
    public function testWildcardEncodingMatchesAnyFormatterInRequestResults() : void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $this->headers->add('Content-Type', 'application/json; charset=*', true);
        $result = $this->negotiator->negotiateRequestContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('application/json', $result->getMediaType());
        $this->assertEquals('utf-8', $result->getEncoding());
    }

    /**
     * Tests that a wildcard encoding matches any formatter in a response result
     */
    public function testWildcardEncodingMatchesAnyFormatterInResponseResults() : void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $formatter->expects($this->once())
            ->method('getSupportedEncodings')
            ->willReturn(['utf-8']);
        $this->headers->add('Accept', 'application/json', true);
        $this->headers->add('Accept-Charset', '*', true);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('application/json', $result->getMediaType());
        $this->assertEquals('utf-8', $result->getEncoding());
    }

    /**
     * Tests that zero quality score encodings are not matched
     */
    public function testZeroQualityScoreEncodingsAreNotMatched() : void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $this->headers->add('Accept', 'application/json', true);
        $this->headers->add('Accept-Charset', 'utf-8; q=0.0', true);
        $result = $this->negotiator->negotiateResponseContent($this->request, [$formatter]);
        $this->assertSame($formatter, $result->getFormatter());
        $this->assertEquals('application/json', $result->getMediaType());
        $this->assertNull($result->getEncoding());
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
