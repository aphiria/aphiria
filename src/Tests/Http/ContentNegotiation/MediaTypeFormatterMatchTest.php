<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use Opulence\Net\Http\ContentNegotiation\MediaTypeFormatterMatch;
use Opulence\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Opulence\Net\Http\Headers\ContentTypeHeaderValue;
use PHPUnit\Framework\TestCase;

/**
 * Tests the media type formatter match result
 */
class MediaTypeFormatterMatchTest extends TestCase
{
    public function testGettingFormatterReturnsSameOneInConstructor(): void
    {
        /** @var IMediaTypeFormatter $formatter */
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeHeaderValue = new ContentTypeHeaderValue('foo/bar');
        $match = new MediaTypeFormatterMatch($formatter, 'baz/blah', $mediaTypeHeaderValue);
        $this->assertSame($formatter, $match->getFormatter());
    }

    public function testGettingMediaTypeReturnsSameOneInConstructor(): void
    {
        /** @var IMediaTypeFormatter $formatter */
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeHeaderValue = new ContentTypeHeaderValue('foo/bar');
        $match = new MediaTypeFormatterMatch($formatter, 'baz/blah', $mediaTypeHeaderValue);
        $this->assertEquals('baz/blah', $match->getMediaType());
    }

    public function testGettingMediaTypeHeaderReturnsSameOneInConstructor(): void
    {
        /** @var IMediaTypeFormatter $formatter */
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeHeaderValue = new ContentTypeHeaderValue('foo/bar');
        $match = new MediaTypeFormatterMatch($formatter, 'baz/blah', $mediaTypeHeaderValue);
        $this->assertSame($mediaTypeHeaderValue, $match->getMediaTypeHeaderValue());
    }
}
