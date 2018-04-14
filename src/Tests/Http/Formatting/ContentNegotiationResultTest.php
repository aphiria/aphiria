<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use Opulence\Net\Http\Formatting\ContentNegotiationResult;
use Opulence\Net\Http\Formatting\IMediaTypeFormatter;

/**
 * Tests the content negotiation result
 */
class ContentNegotiationResultTest extends \PHPUnit\Framework\TestCase
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
