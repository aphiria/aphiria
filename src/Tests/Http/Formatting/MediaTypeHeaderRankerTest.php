<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use Opulence\Net\Http\Formatting\MediaTypeHeaderRanker;
use Opulence\Net\Http\Formatting\MediaTypeHeaderValue;

/**
 * Tests the media type header ranker
 */
class MediaTypeHeaderRankerTest extends \PHPUnit\Framework\TestCase
{
    /** @var MediaTypeHeaderRanker The ranker to use in tests */
    private $ranker;

    /**
     * Sets up the tests
     */
    public function setUp() : void
    {
        $this->ranker = new MediaTypeHeaderRanker();
    }

    /**
     * Tests that ranking an empty list of header values returns an empty array
     */
    public function testRankingEmptyListOfHeaderValuesReturnsEmptyArray() : void
    {
        $this->assertEquals([], $this->ranker->rankMediaTypeHeaders([]));
    }

    /**
     * Tests that ranking returns equal quality media types in order of descending specificity
     */
    public function testRankingReturnsEqualQualityMediaTypesInOrderOfDescendingSpecificity() : void
    {
        // Order here is important - make sure this is in the exact wrong order to verify it's reordered
        $mediaTypeHeaders = [
            new MediaTypeHeaderValue('text/*'),
            new MediaTypeHeaderValue('text/html'),
            new MediaTypeHeaderValue('*/*')
        ];
        $actualRankedMediaTypeHeaders = $this->ranker->rankMediaTypeHeaders($mediaTypeHeaders);
        $this->assertCount(3, $actualRankedMediaTypeHeaders);
        $this->assertSame($mediaTypeHeaders[1], $actualRankedMediaTypeHeaders[0]);
        $this->assertSame($mediaTypeHeaders[0], $actualRankedMediaTypeHeaders[1]);
        $this->assertSame($mediaTypeHeaders[2], $actualRankedMediaTypeHeaders[2]);
    }

    /**
     * Tests that ranking returns in order of quality score
     */
    public function testRankingReturnsInOrderOfQualityScore() : void
    {
        // Order here is important - make sure this is in the exact wrong order to verify it's reordered
        $mediaTypeHeaders = [
            new MediaTypeHeaderValue('text/xml', .5),
            new MediaTypeHeaderValue('text/html', 1),
            new MediaTypeHeaderValue('text/plain', .1)
        ];
        $actualRankedMediaTypeHeaders = $this->ranker->rankMediaTypeHeaders($mediaTypeHeaders);
        $this->assertCount(3, $actualRankedMediaTypeHeaders);
        $this->assertSame($mediaTypeHeaders[1], $actualRankedMediaTypeHeaders[0]);
        $this->assertSame($mediaTypeHeaders[0], $actualRankedMediaTypeHeaders[1]);
        $this->assertSame($mediaTypeHeaders[2], $actualRankedMediaTypeHeaders[2]);
    }

    /**
     * Tests that ranking zero quality score types are filtered out
     */
    public function testRankingZeroQualityScoreTypesAreFilteredOut() : void
    {
        $mediaTypeHeaders = [
            new MediaTypeHeaderValue('text/html', 1),
            new MediaTypeHeaderValue('text/xml', 0)
        ];
        $actualRankedMediaTypeHeaders = $this->ranker->rankMediaTypeHeaders($mediaTypeHeaders);
        $this->assertCount(1, $actualRankedMediaTypeHeaders);
        $this->assertSame($mediaTypeHeaders[0], $actualRankedMediaTypeHeaders[0]);
    }
}
