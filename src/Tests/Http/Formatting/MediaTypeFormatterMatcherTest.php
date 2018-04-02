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
use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\Formatting\IMediaTypeFormatter;
use Opulence\Net\Http\Formatting\MediaTypeFormatterMatcher;
use Opulence\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use Opulence\Net\Http\Headers\ContentTypeHeaderValue;

/**
 * Tests the media type formatter matcher
 */
class MediaTypeFormatterMatcherTest extends \PHPUnit\Framework\TestCase
{
    /** @var MediaTypeFormatterMatcher The matcher to use in tests */
    private $matcher;

    /**
     * Sets up the tests
     */
    public function setUp(): void
    {
        $this->matcher = new MediaTypeFormatterMatcher();
    }

    /**
     * Tests that the best formatter can match a wildcard sub-type
     */
    public function testBestFormatterCanMatchWithWildcardSubType(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter2 = $this->createFormatterMock(['text/html'], 1);
        $acceptHeader = new AcceptMediaTypeHeaderValue('text/*');
        $match = $this->matcher->getBestMediaTypeFormatterMatch([$formatter1, $formatter2], [$acceptHeader]);
        $this->assertSame($formatter2, $match->getFormatter());
        $this->assertEquals('text/html', $match->getMediaType());
    }

    /**
     * Tests that the best formatter can match a wildcard type
     */
    public function testBestFormatterCanMatchWithWildcardType(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter2 = $this->createFormatterMock(['text/html'], 0);
        $acceptHeader = new AcceptMediaTypeHeaderValue('*/*');
        $match = $this->matcher->getBestMediaTypeFormatterMatch([$formatter1, $formatter2], [$acceptHeader]);
        $this->assertSame($formatter1, $match->getFormatter());
        $this->assertEquals('application/json', $match->getMediaType());
    }

    /**
     * Tests that the best formatter is selected by matching the supported media types in the Content-Type header
     */
    public function testBestFormatterIsSelectedByMatchingSupportedMediaTypesInContentTypeHeader(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter2 = $this->createFormatterMock(['text/html'], 1);
        $contentTypeHeader = new ContentTypeHeaderValue('text/html');
        $match = $this->matcher->getBestMediaTypeFormatterMatch([$formatter1, $formatter2], [$contentTypeHeader]);
        $this->assertSame($formatter2, $match->getFormatter());
        $this->assertEquals('text/html', $match->getMediaType());
    }

    /**
     * Tests that the best formatter matches the most specific media type with equal quality media types
     */
    public function testBestFormatterMatchesMostSpecificMediaTypeWithEqualQualityMediaTypes(): void
    {
        $formatter1 = $this->createFormatterMock(['text/plain'], 1);
        $formatter2 = $this->createFormatterMock(['text/xml'], 1);
        $formatter3 = $this->createFormatterMock(['text/html'], 1);
        $acceptHeaders = [
            new AcceptMediaTypeHeaderValue('*/*'),
            new AcceptMediaTypeHeaderValue('text/*'),
            new AcceptMediaTypeHeaderValue('text/html')
        ];
        $match = $this->matcher->getBestMediaTypeFormatterMatch([$formatter1, $formatter2, $formatter3], $acceptHeaders);
        $this->assertSame($formatter3, $match->getFormatter());
        $this->assertEquals('text/html', $match->getMediaType());
    }

    /**
     * Tests that the best formatter can match a wildcard sub-type with a higher quality score than a specific media type
     */
    public function testBestFormatterMatchesWildcardSubTypeWithHigherQualityScoreThanSpecificMediaType(): void
    {
        $formatter = $this->createFormatterMock(['text/plain', 'text/html'], 1);
        $acceptHeaders = [
            new AcceptMediaTypeHeaderValue('text/*', new ImmutableHashTable([new KeyValuePair('q', 0.5)])),
            new AcceptMediaTypeHeaderValue('text/html', new ImmutableHashTable([new KeyValuePair('q', 0.3)]))
        ];
        $match = $this->matcher->getBestMediaTypeFormatterMatch([$formatter], $acceptHeaders);
        $this->assertSame($formatter, $match->getFormatter());
        $this->assertEquals('text/plain', $match->getMediaType());
    }

    /**
     * Tests that the best formatter can match a wildcard type with a higher quality score than a specific media type
     */
    public function tesBestFormatterMatchesWildcardTypeWithHigherQualityScoreThanSpecificMediaType(): void
    {
        $formatter = $this->createFormatterMock(['application/json', 'text/html'], 1);
        $acceptHeaders = [
            new AcceptMediaTypeHeaderValue('*/*', new ImmutableHashTable([new KeyValuePair('q', 0.5)])),
            new AcceptMediaTypeHeaderValue('text/html', new ImmutableHashTable([new KeyValuePair('q', 0.3)]))
        ];
        $match = $this->matcher->getBestMediaTypeFormatterMatch([$formatter], $acceptHeaders);
        $this->assertSame($formatter, $match->getFormatter());
        $this->assertEquals('application/json', $match->getMediaType());
    }

    /**
     * Tests that the best formatter that matches a zero quality media type returns a null match
     */
    public function testBestFormatterThatMatchesZeroQualityMediaTypeReturnsNullMatch(): void
    {
        // The media type should be filtered out of the list of media types to check against
        $formatter = $this->createFormatterMock(['text/html'], 0);
        $acceptHeader = new AcceptMediaTypeHeaderValue(
            'text/html',
            new ImmutableHashTable([new KeyValuePair('q', 0.0)])
        );
        $match = $this->matcher->getBestMediaTypeFormatterMatch([$formatter], [$acceptHeader]);
        $this->assertNull($match);
    }

    /**
     * Tests that getting the best formatter will throw an exception with invalid media types
     */
    public function testBestFormatterWithInvalidMediaTypeThrowsException(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);

        try {
            $acceptHeader = new AcceptMediaTypeHeaderValue('text');
            $this->matcher->getBestMediaTypeFormatterMatch([$formatter], [$acceptHeader]);
            $this->fail('"text" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $acceptHeader = new AcceptMediaTypeHeaderValue('text/');
            $this->matcher->getBestMediaTypeFormatterMatch([$formatter], [$acceptHeader]);
            $this->fail('"text/" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $acceptHeader = new AcceptMediaTypeHeaderValue('/html');
            $this->matcher->getBestMediaTypeFormatterMatch([$formatter], [$acceptHeader]);
            $this->fail('"/html" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
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
