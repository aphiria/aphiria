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

/**
 * Tests the media type formatter matcher
 */
class MediaTypeFormatterMatcherTest /* extends \PHPUnit\Framework\TestCase*/
{
    // Todo: DAVE - I've totally changes this class' public methods, which neccessitates a rewrite of the tests
    /**
     * Tests that the default formatter is always the first formatter
     */
    public function testDefaultFormatterReturnsFirstFormatter() : void
    {
        $formatter1 = $this->createMock(IMediaTypeFormatter::class);
        $matcher = new MediaTypeFormatterMatcher([$formatter1]);
        $this->assertSame($formatter1, $matcher->getDefaultFormatter());
        $formatter2 = $this->createMock(IMediaTypeFormatter::class);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $this->assertSame($formatter1, $matcher->getDefaultFormatter());
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
     * Tests that matching an invalid media type throws an exception
     */
    public function testMatchingInvalidMediaTypeThrowsException() : void
    {
        $matcher = new MediaTypeFormatterMatcher([$this->createMock(IMediaTypeFormatter::class)]);

        try {
            $matcher->getFormatterMatches('foo');
            $this->fail('"foo" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $matcher->getFormatterMatches('foo/');
            $this->fail('"foo/" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $matcher->getFormatterMatches('/foo');
            $this->fail('"/foo" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests that media type matches are returned in the order that formatters are registered
     */
    public function testMediaTypeMatchesAreReturnedInOrderThatFormattersAreRegistered() : void
    {
        $nonMatchingFormatter = $this->createFormatterMock(['text/html'], 1);
        $matchingFormatter1 = $this->createFormatterMock(['application/json'], 1);
        $matchingFormatter2 = $this->createFormatterMock(['application/json'], 1);
        $matcher = new MediaTypeFormatterMatcher([$nonMatchingFormatter, $matchingFormatter1, $matchingFormatter2]);
        $matches = $matcher->getFormatterMatches('application/json');
        $this->assertCount(2, $matches);
        $this->assertSame($matchingFormatter1, $matches[0]->getFormatter());
        $this->assertEquals('application/json', $matches[0]->getMediaType());
        $this->assertSame($matchingFormatter2, $matches[1]->getFormatter());
        $this->assertEquals('application/json', $matches[1]->getMediaType());
    }

    /**
     * Tests that no matching formatters returns an empty array
     */
    public function testNoMatchingFormattersReturnsEmptyArray() : void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->assertEquals([], $matcher->getFormatterMatches('text/html'));
    }

    /**
     * Tests that a wildcard sub-type matches formatters with matching types
     */
    public function testWildcardSubTypeMatchesFormattersWithMatchingType() : void
    {
        $nonMatchingFormatter = $this->createFormatterMock(['text/html'], 1);
        $matchingFormatter = $this->createFormatterMock(['application/json'], 1);
        $matcher = new MediaTypeFormatterMatcher([$nonMatchingFormatter, $matchingFormatter]);
        $matches = $matcher->getFormatterMatches('application/*');
        $this->assertCount(1, $matches);
        $this->assertSame($matchingFormatter, $matches[0]->getFormatter());
        $this->assertEquals('application/json', $matches[0]->getMediaType());
    }

    /**
     * Tests that a wildcard type matches all formatters and returns the first supported media type
     */
    public function testWildcardTypeMatchesAllFormattersAndReturnsFirstSupportedMediaType() : void
    {
        $formatter1 = $this->createFormatterMock(['text/html', 'text/xml'], 1);
        $formatter2 = $this->createFormatterMock(['application/json', 'text/json'], 1);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $matches = $matcher->getFormatterMatches('*/*');
        $this->assertCount(2, $matches);
        $this->assertSame($formatter1, $matches[0]->getFormatter());
        $this->assertEquals('text/html', $matches[0]->getMediaType());
        $this->assertSame($formatter2, $matches[1]->getFormatter());
        $this->assertEquals('application/json', $matches[1]->getMediaType());
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
