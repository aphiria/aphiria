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
use Aphiria\Net\Http\Headers\MediaTypeHeaderValue;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MediaTypeHeaderValueTest extends TestCase
{
    public function testGettingCharsetReturnsOneSetInConstructor(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $value = new MediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertEquals('utf-8', $value->getCharset());
    }

    public function testGettingMediaTypeReturnsOneSetInConstructor(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $value = new MediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertEquals('foo/bar', $value->getMediaType());
    }
    public function testGettingParametersReturnsOnesSetInConstructor(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $value = new MediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertSame($parameters, $value->getParameters());
    }

    public function testGettingSubTypeReturnsCorrectSubType(): void
    {
        $value = new MediaTypeHeaderValue('foo/bar', $this->createMock(IImmutableDictionary::class));
        $this->assertEquals('bar', $value->getSubType());
    }

    public function testGettingSubTypeWithoutSuffixForSubTypeWithoutSuffixReturnsCorrectSubType(): void
    {
        $value = new MediaTypeHeaderValue('foo/bar', $this->createMock(IImmutableDictionary::class));
        $this->assertEquals('bar', $value->getSubTypeWithoutSuffix());
    }

    public function testGettingTypeReturnsCorrectType(): void
    {
        $value = new MediaTypeHeaderValue('foo/bar', $this->createMock(IImmutableDictionary::class));
        $this->assertEquals('foo', $value->getType());
    }

    public function testTypeWithSuffixSetsTypeSubTypeAndSuffixesCorrectly(): void
    {
        $value = new MediaTypeHeaderValue('application/foo+json', $this->createMock(IImmutableDictionary::class));
        $this->assertEquals('application', $value->getType());
        $this->assertEquals('foo+json', $value->getSubType());
        $this->assertEquals('foo', $value->getSubTypeWithoutSuffix());
        $this->assertEquals('json', $value->getSuffix());
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
     */
    public function testIncorrectlyFormattedMediaTypeThrowsException($incorrectlyFormattedMediaType): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Media type must be in format {type}/{sub-type}, received {$incorrectlyFormattedMediaType}");
        new MediaTypeHeaderValue($incorrectlyFormattedMediaType, $this->createMock(IImmutableDictionary::class));
    }
}
