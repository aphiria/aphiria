<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\ContentNegotiation;

use Aphiria\Net\Http\ContentNegotiation\ContentNegotiationResult;
use Aphiria\Net\Http\ContentNegotiation\MediaTypeFormatters\IMediaTypeFormatter;
use PHPUnit\Framework\TestCase;

class ContentNegotiationResultTest extends TestCase
{
    public function testGettingEncodingReturnsSameOneInConstructor(): void
    {
        /** @var IMediaTypeFormatter $formatter */
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $results = new ContentNegotiationResult($formatter, 'foo/bar', 'utf-8', null);
        $this->assertEquals('utf-8', $results->getEncoding());
    }

    public function testGettingFormatterReturnsSameOneInConstructor(): void
    {
        /** @var IMediaTypeFormatter $formatter */
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $results = new ContentNegotiationResult($formatter, 'foo/bar', null, null);
        $this->assertSame($formatter, $results->getFormatter());
    }

    public function testGettingLanguageReturnsSameOneInConstructor(): void
    {
        /** @var IMediaTypeFormatter $formatter */
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $results = new ContentNegotiationResult($formatter, 'foo/bar', 'utf-8', 'en-US');
        $this->assertEquals('en-US', $results->getLanguage());
    }

    public function testGettingMediaTypeReturnsSameOneInConstructor(): void
    {
        /** @var IMediaTypeFormatter $formatter */
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $results = new ContentNegotiationResult($formatter, 'foo/bar', null, null);
        $this->assertEquals('foo/bar', $results->getMediaType());
    }
}
