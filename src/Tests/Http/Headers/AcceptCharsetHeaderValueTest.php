<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
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
    public function testExceptionThrownWithQualityScoreOutsideAcceptedRange(): void
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

    public function testGettingCharsetReturnsSameOneSetInConstructor(): void
    {
        $parameters = $this->createMock(IImmutableDictionary::class);
        $value = new AcceptCharsetHeaderValue('utf-8', $parameters);
        $this->assertSame('utf-8', $value->getCharset());
    }

    public function testGettingParametersReturnsSameOneSetInConstructor(): void
    {
        $parameters = new ImmutableHashTable([]);
        $value = new AcceptCharsetHeaderValue('utf-8', $parameters);
        $this->assertSame($parameters, $value->getParameters());
    }

    public function testGettingQualityReturnsCorrectQuality(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('q', '.5')]);
        $value = new AcceptCharsetHeaderValue('utf-8', $parameters);
        $this->assertEquals(.5, $value->getQuality());
    }

    public function testQualityDefaultsToOne(): void
    {
        $value = new AcceptCharsetHeaderValue('utf-8', null);
        $this->assertEquals(1, $value->getQuality());
    }
}
