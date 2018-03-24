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
use Opulence\Net\Http\Headers\AcceptLanguageHeaderValue;

/**
 * Tests the Accept-Language header value
 */
class AcceptLanguageHeaderValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that an exception is thrown with a quality score outside the accepted range
     */
    public function testExceptionThrownWithQualityScoreOutsideAcceptedRange() : void
    {
        try {
            $parameters = new ImmutableHashTable([new KeyValuePair('q', '-1')]);
            new AcceptLanguageHeaderValue('en-US', $parameters);
            $this->fail('Failed to throw exception for quality score less than 0');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            $parameters = new ImmutableHashTable([new KeyValuePair('q', '1.5')]);
            new AcceptLanguageHeaderValue('en-US', $parameters);
            $this->fail('Failed to throw exception for quality score greater than 1');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
    }

    /**
     * Tests that getting the language returns the same one that's set in the constructor
     */
    public function testGettingLanguageReturnsSameOneSetInConstructor() : void
    {
        $parameters = $this->createMock(IImmutableDictionary::class);
        $value = new AcceptLanguageHeaderValue('en-US', $parameters);
        $this->assertSame('en-US', $value->getLanguage());
    }

    /**
     * Tests that getting the parameters returns the same instance that's set in the constructor
     */
    public function testGettingParametersReturnsSameOneSetInConstructor() : void
    {
        $parameters = new ImmutableHashTable([]);
        $value = new AcceptLanguageHeaderValue('en-US', $parameters);
        $this->assertSame($parameters, $value->getParameters());
    }

    /**
     * Tests that getting the quality returns the correct quality
     */
    public function testGettingQualityReturnsCorrectQuality() : void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('q', '.5')]);
        $value = new AcceptLanguageHeaderValue('en-US', $parameters);
        $this->assertEquals(.5, $value->getQuality());
    }

    /**
     * Tests that getting the quality defaults to 1
     */
    public function testQualityDefaultsToOne() : void
    {
        $value = new AcceptLanguageHeaderValue('en-US', null);
        $this->assertEquals(1, $value->getQuality());
    }
}
