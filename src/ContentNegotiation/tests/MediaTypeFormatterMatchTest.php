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

use Aphiria\ContentNegotiation\MediaTypeFormatterMatch;
use Aphiria\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use PHPUnit\Framework\TestCase;

class MediaTypeFormatterMatchTest extends TestCase
{
    public function testGettingFormatterReturnsSameOneInConstructor(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeHeaderValue = new ContentTypeHeaderValue('foo/bar');
        $match = new MediaTypeFormatterMatch($formatter, 'baz/blah', $mediaTypeHeaderValue);
        $this->assertSame($formatter, $match->formatter);
    }

    public function testGettingMediaTypeHeaderReturnsSameOneInConstructor(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeHeaderValue = new ContentTypeHeaderValue('foo/bar');
        $match = new MediaTypeFormatterMatch($formatter, 'baz/blah', $mediaTypeHeaderValue);
        $this->assertSame($mediaTypeHeaderValue, $match->mediaTypeHeaderValue);
    }

    public function testGettingMediaTypeReturnsSameOneInConstructor(): void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $mediaTypeHeaderValue = new ContentTypeHeaderValue('foo/bar');
        $match = new MediaTypeFormatterMatch($formatter, 'baz/blah', $mediaTypeHeaderValue);
        $this->assertSame('baz/blah', $match->mediaType);
    }
}
