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
use Opulence\Net\Http\Formatting\IMediaTypeFormatter;
use Opulence\Net\Http\Formatting\MediaTypeFormatterMatcher;
use Opulence\Net\Http\HttpHeaders;

/**
 * Tests the media type formatter matcher
 */
class MediaTypeFormatterMatcherTest extends \PHPUnit\Framework\TestCase
{
    /** @var HttpHeaders The headers to use in tests */
    private $headers;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->headers = new HttpHeaders();
    }

    /**
     * Tests that an empty list of formatters throws an exception
     */
    public function testEmptyListOfFormattersThrowsException() : void
    {
        $this->expectException(InvalidArgumentException::class);
        new MediaTypeFormatterMatcher([]);
    }

    /**
     * Tests that the matcher selects the read formatter that supports its content type
     */
    public function testMatcherSelectsReadFormatterThatSupportsContentType() : void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter2 = $this->createFormatterMock(['text/html'], 1);
        $this->headers->add('Content-Type', 'text/html');
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->matchReadMediaTypeFormatter($this->headers);
        $this->assertSame($formatter2, $match->getFormatter());
        $this->assertEquals('text/html', $match->getMediaType());
    }

    /**
     * Tests that matching a write formatter with an invalid media type throws an exception
     */
    public function testMatchingWriteFormatterWithInvalidMediaTypeThrowsException() : void
    {
        $matcher = new MediaTypeFormatterMatcher([$this->createMock(IMediaTypeFormatter::class)]);

        try {
            $this->headers->add('Accept', 'text');
            $matcher->matchWriteMediaTypeFormatter($this->headers);
            $this->fail('"text" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $this->headers->add('Accept', 'text/');
            $matcher->matchWriteMediaTypeFormatter($this->headers);
            $this->fail('"text/" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $this->headers->add('Accept', '/html');
            $matcher->matchWriteMediaTypeFormatter($this->headers);
            $this->fail('"/html" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests that no matching read formatter returns null
     */
    public function testNoMatchingReadFormatterReturnsNull() : void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $this->headers->add('Content-Type', 'text/html');
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $match = $matcher->matchReadMediaTypeFormatter($this->headers);
        $this->assertNull($match);
    }

    /**
     * Tests that no matching write formatter returns null
     */
    public function testNoMatchingWriteFormatterReturnsNull() : void
    {
        $formatter = $this->createFormatterMock(['text/html'], 1);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', 'application/json');
        $this->assertNull($matcher->matchWriteMediaTypeFormatter($this->headers));
    }

    /**
     * Tests that matching the read formatter when no content-type is specified returns the first registered formatter
     */
    public function testReadFormatterIsFirstFormatterRegisteredWithNoContentTypeSpecified() : void
    {
        $formatter1 = $this->createMock(IMediaTypeFormatter::class);
        $formatter2 = $this->createMock(IMediaTypeFormatter::class);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->matchReadMediaTypeFormatter($this->headers);
        $this->assertSame($formatter1, $match->getFormatter());
        $this->assertNull($match->getMediaType());
    }

    /**
     * Tests that matching the write formatter when no Accept is specified returns the first registered formatter
     */
    public function testWriteFormatterIsFirstFormatterRegisteredWithNoAcceptSpecified() : void
    {
        $formatter1 = $this->createMock(IMediaTypeFormatter::class);
        $formatter2 = $this->createMock(IMediaTypeFormatter::class);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->matchReadMediaTypeFormatter($this->headers);
        $this->assertSame($formatter1, $match->getFormatter());
        $this->assertNull($match->getMediaType());
    }

    /**
     * Tests that a write formatter can match a wildcard sub-type
     */
    public function testWriteFormatterCanMatchWithWildcardSubType() : void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter2 = $this->createFormatterMock(['text/html'], 1);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $this->headers->add('Accept', 'text/*', true);
        $match = $matcher->matchWriteMediaTypeFormatter($this->headers);
        $this->assertSame($formatter2, $match->getFormatter());
        $this->assertEquals('text/html', $match->getMediaType());
    }

    /**
     * Tests that a write formatter can match a wildcard type
     */
    public function testWriteFormatterCanMatchWithWildcardType() : void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter2 = $this->createFormatterMock(['text/html'], 0);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $this->headers->add('Accept', '*/*', true);
        $match = $matcher->matchWriteMediaTypeFormatter($this->headers);
        $this->assertSame($formatter1, $match->getFormatter());
        $this->assertEquals('application/json', $match->getMediaType());
    }

    /**
     * Tests that a write formatter matches the most specific media type with equal quality media types
     */
    public function testWriteFormatterMatchesMostSpecificMediaTypeWithEqualQualityMediaTypes() : void
    {
        $formatter1 = $this->createFormatterMock(['text/plain'], 1);
        $formatter2 = $this->createFormatterMock(['text/xml'], 1);
        $formatter3 = $this->createFormatterMock(['text/html'], 1);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2, $formatter3]);
        $this->headers->add('Accept', '*/*', true);
        $this->headers->add('Accept', 'text/*', true);
        $this->headers->add('Accept', 'text/html', true);
        $match = $matcher->matchWriteMediaTypeFormatter($this->headers);
        $this->assertSame($formatter3, $match->getFormatter());
        $this->assertEquals('text/html', $match->getMediaType());
    }

    /**
     * Tests that a write formatter can match a wildcard sub-type with a higher quality score than a specific media type
     */
    public function testWriteFormatterMatchesWildcardSubTypeWithHigherQualityScoreThanSpecificMediaType() : void
    {
        $formatter = $this->createFormatterMock(['text/plain', 'text/html'], 1);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', 'text/*; q=0.5', true);
        $this->headers->add('Accept', 'text/html; q=0.3', true);
        $match = $matcher->matchWriteMediaTypeFormatter($this->headers);
        $this->assertSame($formatter, $match->getFormatter());
        $this->assertEquals('text/plain', $match->getMediaType());
    }

    /**
     * Tests that a write formatter can match a wildcard type with a higher quality score than a specific media type
     */
    public function testWriteFormatterMatchesWildcardTypeWithHigherQualityScoreThanSpecificMediaType() : void
    {
        $formatter = $this->createFormatterMock(['application/json', 'text/html'], 1);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', '*/*; q=0.5', true);
        $this->headers->add('Accept', 'text/html; q=0.3', true);
        $match = $matcher->matchWriteMediaTypeFormatter($this->headers);
        $this->assertSame($formatter, $match->getFormatter());
        $this->assertEquals('application/json', $match->getMediaType());
    }

    /**
     * Tests that a write formatter that matches a zero quality media type returns a null match
     */
    public function testWriteFormatterThatMatchesZeroQualityMediaTypeReturnsNullMatch() : void
    {
        // The media type should be filtered out of the list of media types to check against
        $formatter = $this->createFormatterMock(['text/html'], 0);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', 'text/html; q=0.0');
        $match = $matcher->matchWriteMediaTypeFormatter($this->headers);
        $this->assertNull($match);
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
