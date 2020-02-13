<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Headers;

use Aphiria\Net\Http\Headers\AcceptCharsetHeaderValue;
use InvalidArgumentException;
use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Collections\KeyValuePair;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Accept-Charset header value
 */
class AcceptCharsetHeaderValueTest extends TestCase
{
    public function qualityScoreOutsideAcceptedRangeProvider(): array
    {
        return [
            ['-1'],
            ['1.5'],
        ];
    }

    /**
     * @dataProvider qualityScoreOutsideAcceptedRangeProvider
     */
    public function testExceptionThrownWithQualityScoreOutsideAcceptedRange($invalidScore): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('q', $invalidScore)]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quality score must be between 0 and 1, inclusive');
        new AcceptCharsetHeaderValue('utf-8', $parameters);
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
