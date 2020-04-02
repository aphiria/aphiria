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

use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Headers\AcceptLanguageHeaderValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Accept-Language header value
 */
class AcceptLanguageHeaderValueTest extends TestCase
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
        new AcceptLanguageHeaderValue('en-US', $parameters);
    }

    public function testGettingLanguageReturnsSameOneSetInConstructor(): void
    {
        $parameters = $this->createMock(IImmutableDictionary::class);
        $value = new AcceptLanguageHeaderValue('en-US', $parameters);
        $this->assertSame('en-US', $value->getLanguage());
    }

    public function testGettingParametersReturnsSameOneSetInConstructor(): void
    {
        $parameters = new ImmutableHashTable([]);
        $value = new AcceptLanguageHeaderValue('en-US', $parameters);
        $this->assertSame($parameters, $value->getParameters());
    }

    public function testGettingQualityReturnsCorrectQuality(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('q', '.5')]);
        $value = new AcceptLanguageHeaderValue('en-US', $parameters);
        $this->assertEquals(.5, $value->getQuality());
    }

    public function testQualityDefaultsToOne(): void
    {
        $value = new AcceptLanguageHeaderValue('en-US', null);
        $this->assertEquals(1, $value->getQuality());
    }
}
