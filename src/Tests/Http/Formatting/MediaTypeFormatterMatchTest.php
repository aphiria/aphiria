<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use Opulence\Net\Http\Formatting\IMediaTypeFormatter;
use Opulence\Net\Http\Formatting\MediaTypeFormatterMatch;
use Opulence\Net\Http\Headers\ContentTypeHeaderValue;

/**
 * Tests the media type formatter match result
 */
class MediaTypeFormatterMatchTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that getting the formatter returns the same one in the constructor
     */
    public function testGettingFormatterReturnsSameOneInConstructor(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeHeaderValue = new ContentTypeHeaderValue('foo/bar');
        $match = new MediaTypeFormatterMatch($formatter, 'baz/blah', $mediaTypeHeaderValue);
        $this->assertSame($formatter, $match->getFormatter());
    }

    /**
     * Tests that getting the media type returns the same one in the constructor
     */
    public function testGettingMediaTypeReturnsSameOneInConstructor(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeHeaderValue = new ContentTypeHeaderValue('foo/bar');
        $match = new MediaTypeFormatterMatch($formatter, 'baz/blah', $mediaTypeHeaderValue);
        $this->assertEquals('baz/blah', $match->getMediaType());
    }

    /**
     * Tests that getting the media type header returns the same one in the constructor
     */
    public function testGettingMediaTypeHeaderReturnsSameOneInConstructor(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeHeaderValue = new ContentTypeHeaderValue('foo/bar');
        $match = new MediaTypeFormatterMatch($formatter, 'baz/blah', $mediaTypeHeaderValue);
        $this->assertSame($mediaTypeHeaderValue, $match->getMediaTypeHeaderValue());
    }
}
