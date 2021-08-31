<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Net\Tests\Http\Headers;

use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Net\Http\Headers\MediaTypeHeaderValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MediaTypeHeaderValueTest extends TestCase
{
    public function testGettingCharsetReturnsOneSetInConstructor(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $value = new MediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertSame('utf-8', $value->getCharset());
    }

    public function testGettingMediaTypeReturnsOneSetInConstructor(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $value = new MediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertSame('foo/bar', $value->getMediaType());
    }
    public function testGettingParametersReturnsOnesSetInConstructor(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $value = new MediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertSame($parameters, $value->getParameters());
    }

    public function testGettingSubTypeReturnsCorrectSubType(): void
    {
        /** @var IImmutableDictionary<string, string|null> $parameters */
        $parameters = $this->createMock(IImmutableDictionary::class);
        $value = new MediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertSame('bar', $value->getSubType());
    }

    public function testGettingSubTypeWithoutSuffixForSubTypeWithoutSuffixReturnsCorrectSubType(): void
    {
        /** @var IImmutableDictionary<string, string|null> $parameters */
        $parameters = $this->createMock(IImmutableDictionary::class);
        $value = new MediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertSame('bar', $value->getSubTypeWithoutSuffix());
    }

    public function testGettingTypeReturnsCorrectType(): void
    {
        /** @var IImmutableDictionary<string, string|null> $parameters */
        $parameters = $this->createMock(IImmutableDictionary::class);
        $value = new MediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertSame('foo', $value->getType());
    }

    public function testTypeWithSuffixSetsTypeSubTypeAndSuffixesCorrectly(): void
    {
        /** @var IImmutableDictionary<string, string|null> $parameters */
        $parameters = $this->createMock(IImmutableDictionary::class);
        $value = new MediaTypeHeaderValue('application/foo+json', $parameters);
        $this->assertSame('application', $value->getType());
        $this->assertSame('foo+json', $value->getSubType());
        $this->assertSame('foo', $value->getSubTypeWithoutSuffix());
        $this->assertSame('json', $value->getSuffix());
    }

    public function incorrectlyFormattedMediaTypeProvider(): array
    {
        return [
            ['foo'],
            ['foo/'],
            ['/foo'],
        ];
    }

    /**
     * @dataProvider incorrectlyFormattedMediaTypeProvider
     * @param string $incorrectlyFormattedMediaType The incorrectly-formatted media type
     */
    public function testIncorrectlyFormattedMediaTypeThrowsException(string $incorrectlyFormattedMediaType): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Media type must be in format {type}/{sub-type}, received {$incorrectlyFormattedMediaType}");
        /** @var IImmutableDictionary<string, string|null> $parameters */
        $parameters = $this->createMock(IImmutableDictionary::class);
        new MediaTypeHeaderValue($incorrectlyFormattedMediaType, $parameters);
    }
}
