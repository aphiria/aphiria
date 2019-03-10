<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/net/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Headers;

use Aphiria\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use InvalidArgumentException;
use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Accept media type header value
 */
class AcceptMediaTypeHeaderValueTest extends TestCase
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
        new AcceptMediaTypeHeaderValue('foo/bar', $parameters);
    }

    public function testGettingQualityReturnsCorrectQuality(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('q', '.5')]);
        $value = new AcceptMediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertEquals(.5, $value->getQuality());
    }

    public function testQualityDefaultsToOne(): void
    {
        $value = new AcceptMediaTypeHeaderValue('foo/bar', null);
        $this->assertEquals(1, $value->getQuality());
    }
}
