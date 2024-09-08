<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Headers;

use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Headers\AcceptCharsetHeaderValue;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class AcceptCharsetHeaderValueTest extends TestCase
{
    /**
     * @param string $invalidScore The invalid score
     */
    #[TestWith(['-1'])]
    #[TestWith(['1.5'])]
    public function testExceptionThrownWithQualityScoreOutsideAcceptedRange(string $invalidScore): void
    {
        /** @var ImmutableHashTable<string, string|null> $parameters */
        $parameters = new ImmutableHashTable([new KeyValuePair('q', $invalidScore)]);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Quality score must be between 0 and 1, inclusive');
        new AcceptCharsetHeaderValue('utf-8', $parameters);
    }

    public function testGettingCharsetReturnsSameOneSetInConstructor(): void
    {
        /** @var ImmutableHashTable<string, string|null> $parameters */
        $parameters = new ImmutableHashTable([]);
        $value = new AcceptCharsetHeaderValue('utf-8', $parameters);
        $this->assertSame('utf-8', $value->charset);
    }

    public function testGettingParametersReturnsSameOneSetInConstructor(): void
    {
        /** @var ImmutableHashTable<string, string|null> $parameters */
        $parameters = new ImmutableHashTable([]);
        $value = new AcceptCharsetHeaderValue('utf-8', $parameters);
        $this->assertSame($parameters, $value->parameters);
    }

    public function testGettingQualityReturnsCorrectQuality(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('q', '.5')]);
        $value = new AcceptCharsetHeaderValue('utf-8', $parameters);
        $this->assertSame(.5, $value->getQuality());
    }

    public function testQualityDefaultsToOne(): void
    {
        $value = new AcceptCharsetHeaderValue('utf-8', null);
        $this->assertEquals(1.0, $value->getQuality());
    }
}
