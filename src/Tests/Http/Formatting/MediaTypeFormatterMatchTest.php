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

/**
 * Tests the media type formatter match
 */
class MediaTypeFormatterMatchTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that getting the formatter returns the same one in the constructor
     */
    public function testGettingFormatterReturnsSameOneInConstructor() : void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $match = new MediaTypeFormatterMatch($formatter, 'foo/bar');
        $this->assertSame($formatter, $match->getFormatter());
    }

    /**
     * Tests that getting the media type returns the same one in the constructor
     */
    public function testGettingMediaTypeReturnsSameOneInConstructor() : void
    {
        $formatter = $this->createMock(IMediaTypeFormatter::class);
        $match = new MediaTypeFormatterMatch($formatter, 'foo/bar');
        $this->assertEquals('foo/bar', $match->getMediaType());
    }
}
