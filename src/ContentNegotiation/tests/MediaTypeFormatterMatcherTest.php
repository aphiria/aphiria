<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\ContentNegotiation\Tests;

use Aphiria\ContentNegotiation\MediaTypeFormatterMatcher;
use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\ContentNegotiation\Tests\Mocks\User;
use Aphiria\Net\Http\Headers;
use Aphiria\Net\Http\IRequest;
use Aphiria\Net\Http\Request;
use Aphiria\Net\Uri;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MediaTypeFormatterMatcherTest extends TestCase
{
    private Headers $headers;
    private IRequest $request;

    protected function setUp(): void
    {
        $this->headers = new Headers();
        $this->request = new Request('GET', new Uri('http://example.com'), $this->headers);
    }

    public function testBestFormatterCanMatchWithWildcardSubType(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter2 = $this->createFormatterMock(['text/html'], 1);
        $formatter2->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', 'text/*');
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter2, $match->getFormatter());
        $this->assertEquals('text/html', $match->getMediaType());
    }

    public function testBestFormatterCanMatchWithWildcardType(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter2 = $this->createFormatterMock(['text/html'], 0);
        $this->headers->add('Accept', '*/*');
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter1, $match->getFormatter());
        $this->assertEquals('application/json', $match->getMediaType());
    }

    public function testBestFormatterIsFirstSupportedWhenAllContentTypesAreEqualScoreAndHaveNoWildcards(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter2 = $this->createFormatterMock(['text/json'], 0);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $this->headers->add('Accept', 'application/json');
        $this->headers->add('Accept', 'text/json', true);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter1, $match->getFormatter());
        $this->assertEquals('application/json', $match->getMediaType());
    }

    public function testBestFormatterIsFirstSupportedWhenAllContentTypesAreEqualScoresAndOneHasWildcardType(): void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', '*/*');
        $this->headers->add('Accept', 'application/*', true);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter, $match->getFormatter());
        $this->assertEquals('application/json', $match->getMediaType());
    }

    public function testBestFormatterIsFirstSupportedWhenAllContentTypesAreEqualScoreWildcardSubTypes(): void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', 'application/*');
        $this->headers->add('Accept', 'application/*', true);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter, $match->getFormatter());
        $this->assertEquals('application/json', $match->getMediaType());
    }

    public function testBestFormatterIsFirstSupportedWhenAllContentTypesAreEqualScoreAndOneHasWilcardSubTypeAndOtherDoesNot(): void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', 'application/*');
        $this->headers->add('Accept', 'application/json', true);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter, $match->getFormatter());
        $this->assertEquals('application/json', $match->getMediaType());
    }

    public function testBestFormatterIsFirstSupportedWhenAllContentTypesAreEqualScoreWildcardTypes(): void
    {
        $formatter = $this->createFormatterMock(['application/json'], 1);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', '*/*');
        $this->headers->add('Accept', '*/*', true);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter, $match->getFormatter());
        $this->assertEquals('application/json', $match->getMediaType());
    }

    public function testBestFormatterIsSelectedByMatchingSupportedMediaTypesInContentTypeHeader(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter1->expects($this->once())
            ->method('canReadType')
            ->with(User::class)
            ->willReturn(true);
        $formatter2 = $this->createFormatterMock(['text/html'], 1);
        $formatter2->expects($this->once())
            ->method('canReadType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Content-Type', 'text/html');
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestRequestMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter2, $match->getFormatter());
        $this->assertEquals('text/html', $match->getMediaType());
    }

    public function testBestFormatterMatchesMostSpecificMediaTypeWithEqualQualityMediaTypes(): void
    {
        $formatter1 = $this->createFormatterMock(['text/plain'], 1);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter2 = $this->createFormatterMock(['text/xml'], 1);
        $formatter2->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter3 = $this->createFormatterMock(['text/html'], 1);
        $formatter3->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', '*/*');
        $this->headers->add('Accept', 'text/*', true);
        $this->headers->add('Accept', 'text/html', true);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2, $formatter3]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter3, $match->getFormatter());
        $this->assertEquals('text/html', $match->getMediaType());
    }

    public function testBestFormatterMatchesWildcardSubTypeWithHigherQualityScoreThanSpecificMediaType(): void
    {
        $formatter = $this->createFormatterMock(['text/plain', 'text/html'], 1);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', 'text/*; q=0.5');
        $this->headers->add('Accept', 'text/html; q=0.3', true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter, $match->getFormatter());
        $this->assertEquals('text/plain', $match->getMediaType());
    }

    public function testBestFormatterMatchesHigherQualityScoreWhenBothMediaTypesAreFullyQualified(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter2 = $this->createFormatterMock(['text/json'], 1);
        $formatter2->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', 'application/json; q=0.3');
        $this->headers->add('Accept', 'text/json; q=0.5', true);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter2, $match->getFormatter());
        $this->assertEquals('text/json', $match->getMediaType());
    }

    public function tesBestFormatterMatchesWildcardTypeWithHigherQualityScoreThanSpecificMediaType(): void
    {
        $formatter = $this->createFormatterMock(['application/json', 'text/html'], 1);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', '*/*; q=0.5');
        $this->headers->add('Accept', 'text/html; q=0.3', true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter, $match->getFormatter());
        $this->assertEquals('application/json', $match->getMediaType());
    }

    public function testBestFormatterThatMatchesZeroQualityMediaTypeReturnsNullMatch(): void
    {
        // The media type should be filtered out of the list of media types to check against
        $formatter = $this->createFormatterMock(['text/html'], 0);
        $this->headers->add('Accept', 'text/html; q=0.0');
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNull($match);
    }

    public function testBestFormatterWithInvalidMediaTypeThrowsException(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);

        try {
            $this->headers->add('Accept', 'text');
            $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
            $this->fail('"text" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $this->headers->add('Accept', 'text/');
            $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
            $this->fail('"text/" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $this->headers->add('Accept', '/html');
            $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
            $this->fail('"/html" is not a valid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
    }

    public function testBestRequestFormatterIsSkippedIfItCannotReadType(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter1->expects($this->once())
            ->method('canReadType')
            ->with(User::class)
            ->willReturn(false);
        $formatter2 = $this->createFormatterMock(['text/html'], 1);
        $formatter2->expects($this->once())
            ->method('canReadType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Content-Type', '*/*');
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestRequestMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter2, $match->getFormatter());
        $this->assertEquals('text/html', $match->getMediaType());
    }

    public function testBestResponseFormatterIsSkippedIfItCannotWriteType(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json'], 1);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(false);
        $formatter2 = $this->createFormatterMock(['text/html'], 1);
        $formatter2->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', '*/*');
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertSame($formatter2, $match->getFormatter());
        $this->assertEquals('text/html', $match->getMediaType());
    }

    public function testExceptionIsThrownIfNoMediaTypeFormattersAreSpecified(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('List of formatters cannot be empty');
        new MediaTypeFormatterMatcher([]);
    }

    /**
     * Creates a mock media type formatter with a list of supported media types
     *
     * @param array $supportedMediaTypes The list of supported media types
     * @param int $numTimesSupportedMediaTypesCalled The number of times the formatter's supported media types will be checked
     * @return IMediaTypeFormatter|MockObject The mocked formatter
     */
    private function createFormatterMock(
        array $supportedMediaTypes,
        int $numTimesSupportedMediaTypesCalled
    ): IMediaTypeFormatter {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->expects($this->exactly($numTimesSupportedMediaTypesCalled))
            ->method('getSupportedMediaTypes')
            ->willReturn($supportedMediaTypes);

        return $formatter;
    }
}
