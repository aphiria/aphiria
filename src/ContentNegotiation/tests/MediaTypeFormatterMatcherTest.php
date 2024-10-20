<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
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
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
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

    public function tesBestFormatterMatchesWildcardTypeWithHigherQualityScoreThanSpecificMediaType(): void
    {
        $formatter = $this->createFormatterMock(['application/json', 'text/html']);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', '*/*; q=0.5');
        $this->headers->add('Accept', 'text/html; q=0.3', true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter, $match->formatter);
        $this->assertSame('application/json', $match->mediaType);
    }

    public function testBestFormatterCanMatchWithWildcardSubType(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json']);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter2 = $this->createFormatterMock(['text/html']);
        $formatter2->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', 'text/*');
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter2, $match->formatter);
        $this->assertSame('text/html', $match->mediaType);
    }

    public function testBestFormatterCanMatchWithWildcardType(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json']);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter2 = $this->createFormatterMock(['text/html']);
        $this->headers->add('Accept', '*/*');
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter1, $match->formatter);
        $this->assertSame('application/json', $match->mediaType);
    }

    public function testBestFormatterIsFirstSupportedWhenAllContentTypesAreEqualScoreAndHaveNoWildcards(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json']);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter2 = $this->createFormatterMock(['text/json']);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $this->headers->add('Accept', 'application/json');
        $this->headers->add('Accept', 'text/json', true);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter1, $match->formatter);
        $this->assertSame('application/json', $match->mediaType);
    }

    public function testBestFormatterIsFirstSupportedWhenAllContentTypesAreEqualScoreAndOneHasWilcardSubTypeAndOtherDoesNot(): void
    {
        $formatter = $this->createFormatterMock(['application/json']);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', 'application/*');
        $this->headers->add('Accept', 'application/json', true);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter, $match->formatter);
        $this->assertSame('application/json', $match->mediaType);
    }

    public function testBestFormatterIsFirstSupportedWhenAllContentTypesAreEqualScoresAndOneHasWildcardType(): void
    {
        $formatter = $this->createFormatterMock(['application/json']);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', '*/*');
        $this->headers->add('Accept', 'application/*', true);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter, $match->formatter);
        $this->assertSame('application/json', $match->mediaType);
    }

    public function testBestFormatterIsFirstSupportedWhenAllContentTypesAreEqualScoreWildcardSubTypes(): void
    {
        $formatter = $this->createFormatterMock(['application/json']);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', 'application/*');
        $this->headers->add('Accept', 'application/*', true);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter, $match->formatter);
        $this->assertSame('application/json', $match->mediaType);
    }

    public function testBestFormatterIsFirstSupportedWhenAllContentTypesAreEqualScoreWildcardTypes(): void
    {
        $formatter = $this->createFormatterMock(['application/json']);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $this->headers->add('Accept', '*/*');
        $this->headers->add('Accept', '*/*', true);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter, $match->formatter);
        $this->assertSame('application/json', $match->mediaType);
    }

    public function testBestFormatterIsSelectedByMatchingSupportedMediaTypesInContentTypeHeader(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json']);
        $formatter1->expects($this->once())
            ->method('canReadType')
            ->with(User::class)
            ->willReturn(true);
        $formatter2 = $this->createFormatterMock(['text/html']);
        $formatter2->expects($this->once())
            ->method('canReadType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Content-Type', 'text/html');
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestRequestMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter2, $match->formatter);
        $this->assertSame('text/html', $match->mediaType);
    }

    public function testBestFormatterMatchesHigherQualityScoreWhenBothMediaTypesAreFullyQualified(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json']);
        $formatter2 = $this->createFormatterMock(['text/json']);
        $formatter2->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', 'application/json; q=0.3');
        $this->headers->add('Accept', 'text/json; q=0.5', true);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter2, $match->formatter);
        $this->assertSame('text/json', $match->mediaType);
    }

    public function testBestFormatterMatchesMostSpecificMediaTypeWithEqualQualityMediaTypes(): void
    {
        $formatter1 = $this->createFormatterMock(['text/plain']);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter2 = $this->createFormatterMock(['text/xml']);
        $formatter2->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $formatter3 = $this->createFormatterMock(['text/html']);
        $formatter3->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', '*/*');
        $this->headers->add('Accept', 'text/*', true);
        $this->headers->add('Accept', 'text/html', true);
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2, $formatter3]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter3, $match->formatter);
        $this->assertSame('text/html', $match->mediaType);
    }

    public function testBestFormatterMatchesWildcardSubTypeWithHigherQualityScoreThanSpecificMediaType(): void
    {
        $formatter = $this->createFormatterMock(['text/plain', 'text/html']);
        $formatter->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', 'text/*; q=0.5');
        $this->headers->add('Accept', 'text/html; q=0.3', true);
        $matcher = new MediaTypeFormatterMatcher([$formatter]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter, $match->formatter);
        $this->assertSame('text/plain', $match->mediaType);
    }

    public function testBestFormatterThatMatchesZeroQualityMediaTypeReturnsNullMatch(): void
    {
        // The media type should be filtered out of the list of media types to check against
        $formatter = $this->createFormatterMock(['text/html']);
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
        $formatter1 = $this->createFormatterMock(['application/json']);
        $formatter1->expects($this->once())
            ->method('canReadType')
            ->with(User::class)
            ->willReturn(false);
        $formatter2 = $this->createFormatterMock(['text/html']);
        $formatter2->expects($this->once())
            ->method('canReadType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Content-Type', '*/*');
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestRequestMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter2, $match->formatter);
        $this->assertSame('text/html', $match->mediaType);
    }

    public function testBestResponseFormatterIsSkippedIfItCannotWriteType(): void
    {
        $formatter1 = $this->createFormatterMock(['application/json']);
        $formatter1->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(false);
        $formatter2 = $this->createFormatterMock(['text/html']);
        $formatter2->expects($this->once())
            ->method('canWriteType')
            ->with(User::class)
            ->willReturn(true);
        $this->headers->add('Accept', '*/*');
        $matcher = new MediaTypeFormatterMatcher([$formatter1, $formatter2]);
        $match = $matcher->getBestResponseMediaTypeFormatterMatch(User::class, $this->request);
        $this->assertNotNull($match);
        $this->assertSame($formatter2, $match->formatter);
        $this->assertSame('text/html', $match->mediaType);
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
     * @param list<string> $supportedMediaTypes The list of supported media types
     * @return IMediaTypeFormatter&MockObject The mocked formatter
     */
    private function createFormatterMock(array $supportedMediaTypes): IMediaTypeFormatter&MockObject
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $formatter->method(PropertyHook::get('supportedMediaTypes'))
            ->willReturn($supportedMediaTypes);

        return $formatter;
    }
}
