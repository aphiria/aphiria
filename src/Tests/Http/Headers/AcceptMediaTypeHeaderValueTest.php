<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Headers;

use InvalidArgumentException;
use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\Headers\AcceptMediaTypeHeaderValue;

/**
 * Tests the Accept media type header value
 */
class AcceptMediaTypeHeaderValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that an exception is thrown with a quality score outside the accepted range
     */
    public function testExceptionThrownWithQualityScoreOutsideAcceptedRange() : void
    {
        try {
            $parameters = new ImmutableHashTable([new KeyValuePair('q', '-1')]);
            new AcceptMediaTypeHeaderValue('foo/bar', $parameters);
            $this->fail('Failed to throw exception for quality score less than 0');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $parameters = new ImmutableHashTable([new KeyValuePair('q', '1.5')]);
            new AcceptMediaTypeHeaderValue('foo/bar', $parameters);
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
        $parameters = new ImmutableHashTable([new KeyValuePair('q', '.5')]);
        $value = new AcceptMediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertEquals(.5, $value->getQuality());
    }

    /**
     * Tests that getting the quality defaults to 1
     */
    public function testQualityDefaultsToOne() : void
    {
        $value = new AcceptMediaTypeHeaderValue('foo/bar', null);
        $this->assertEquals(1, $value->getQuality());
    }
}
