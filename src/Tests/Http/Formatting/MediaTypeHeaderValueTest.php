<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use InvalidArgumentException;
use Opulence\Net\Http\Formatting\MediaTypeHeaderValue;

/**
 * Tests the media type header value
 */
class MediaTypeHeaderValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that an exception is thrown with a quality score outside the accepted range
     */
    public function testExceptionThrownWithQualityScoreOutsideAcceptedRange() : void
    {
        try {
            new MediaTypeHeaderValue('foo', 'bar', -1);
            $this->fail('Failed to throw exception for quality score less than 0');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            new MediaTypeHeaderValue('foo', 'bar', 1.5);
            $this->fail('Failed to throw exception for quality score greater than 1');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests that getting the quality returns the correct quality
     */
    public function testGettingQualityReturnsCorrectQuality() : void
    {
        $value = new MediaTypeHeaderValue('foo', 'bar', .5);
        $this->assertEquals(.5, $value->getQuality());
    }

    /**
     * Tests that getting the sub-type returns the correct sub-type
     */
    public function testGettingSubTypeReturnsCorrectSubtType() : void
    {
        $value = new MediaTypeHeaderValue('foo', 'bar', 1);
        $this->assertEquals('bar', $value->getSubType());
    }

    /**
     * Tests that getting the type returns the correct type
     */
    public function testGettingTypeReturnsCorrectType() : void
    {
        $value = new MediaTypeHeaderValue('foo', 'bar', 1);
        $this->assertEquals('foo', $value->getType());
    }

    /**
     * Tests that getting the quality defaults to 1
     */
    public function testQualityDefaultsToOne() : void
    {
        $value = new MediaTypeHeaderValue('foo', 'bar', null);
        $this->assertEquals(1, $value->getQuality());
    }
}
