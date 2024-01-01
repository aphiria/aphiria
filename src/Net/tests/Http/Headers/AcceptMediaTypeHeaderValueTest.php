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
use Aphiria\Net\Http\Headers\AcceptMediaTypeHeaderValue;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

class AcceptMediaTypeHeaderValueTest extends TestCase
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
        new AcceptMediaTypeHeaderValue('foo/bar', $parameters);
    }

    public function testGettingQualityReturnsCorrectQuality(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('q', '.5')]);
        $value = new AcceptMediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertSame(.5, $value->getQuality());
    }

    public function testQualityDefaultsToOne(): void
    {
        $value = new AcceptMediaTypeHeaderValue('foo/bar', null);
        $this->assertEquals(1, $value->getQuality());
    }

    public function testTypeWithSuffixSetsTypeSubTypeAndSuffixesCorrectly(): void
    {
        $value = new AcceptMediaTypeHeaderValue('application/foo+json', null);
        $this->assertSame('application', $value->type);
        $this->assertSame('foo+json', $value->subType);
        $this->assertSame('foo', $value->getSubTypeWithoutSuffix());
        $this->assertSame('json', $value->suffix);
    }
}
