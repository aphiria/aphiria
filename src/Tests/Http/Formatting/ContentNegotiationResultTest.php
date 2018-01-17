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
    /**
     * Tests that getting the charset returns the same one in the constructor
     */
    public function testGettingCharSetReturnsSameOneInConstructor() : void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $results = new ContentNegotiationResult($formatter, 'foo/bar', 'utf-8');
        $this->assertEquals('utf-8', $results->getCharSet());
    }

    /**
     * Tests that getting the formatter returns the same one in the constructor
     */
    public function testGettingFormatterReturnsSameOneInConstructor() : void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $results = new ContentNegotiationResult($formatter, 'foo/bar', null);
        $this->assertSame($formatter, $results->getFormatter());
    }

    /**
     * Tests that getting the media type returns the same one in the constructor
     */
    public function testGettingMediaTypeReturnsSameOneInConstructor() : void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $results = new ContentNegotiationResult($formatter, 'foo/bar', null);
        $this->assertEquals('foo/bar', $results->getMediaType());
    }
}
