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
use Opulence\Collections\IImmutableDictionary;
use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\Headers\AcceptCharsetHeaderValue;

/**
 * Tests the Accept-Charset header value
 */
class AcceptCharsetHeaderValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that an exception is thrown with a quality score outside the accepted range
     */
    public function testExceptionThrownWithQualityScoreOutsideAcceptedRange() : void
    {
        try {
            $parameters = new ImmutableHashTable([new KeyValuePair('q', '-1')]);
            new AcceptCharsetHeaderValue('utf-8', $parameters);
            $this->fail('Failed to throw exception for quality score less than 0');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $parameters = new ImmutableHashTable([new KeyValuePair('q', '1.5')]);
            new AcceptCharsetHeaderValue('utf-8', $parameters);
            $this->fail('Failed to throw exception for quality score greater than 1');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests that getting the charset returns the same one that's set in the constructor
     */
    public function testGettingCharsetReturnsSameOneSetInConstructor() : void
    {
        $parameters = $this->createMock(IImmutableDictionary::class);
        $value = new AcceptCharsetHeaderValue('utf-8', $parameters);
        $this->assertSame('utf-8', $value->getCharset());
    }

    /**
     * Tests that getting the parameters returns the same instance that's set in the constructor
     */
    public function testGettingParametersReturnsSameOneSetInConstructor() : void
    {
        $parameters = new ImmutableHashTable([]);
        $value = new AcceptCharsetHeaderValue('utf-8', $parameters);
        $this->assertSame($parameters, $value->getParameters());
    }

    /**
     * Tests that getting the quality returns the correct quality
     */
    public function testGettingQualityReturnsCorrectQuality() : void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('q', '.5')]);
        $value = new AcceptCharsetHeaderValue('utf-8', $parameters);
        $this->assertEquals(.5, $value->getQuality());
    }

    /**
     * Tests that getting the quality defaults to 1
     */
    public function testQualityDefaultsToOne() : void
    {
        $value = new AcceptCharsetHeaderValue('utf-8', null);
        $this->assertEquals(1, $value->getQuality());
    }
}
