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

use Aphiria\Net\Http\Headers\ContentTypeHeaderValue;
use InvalidArgumentException;
use Aphiria\Collections\IImmutableDictionary;
use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Collections\KeyValuePair;
use PHPUnit\Framework\TestCase;

/**
 * Tests the Content-Type header value
 */
class ContentTypeHeaderValueTest extends TestCase
{
    public function testGettingCharsetReturnsOneSetInConstructor(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $value = new ContentTypeHeaderValue('foo/bar', $parameters);
        $this->assertEquals('utf-8', $value->getCharset());
    }

    public function testGettingMediaTypeReturnsOneSetInConstructor(): void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $value = new ContentTypeHeaderValue('foo/bar', $parameters);
        $this->assertEquals('foo/bar', $value->getMediaType());
    }

    public function testGettingSubTypeReturnsCorrectSubtType(): void
    {
        $value = new ContentTypeHeaderValue('foo/bar', $this->createMock(IImmutableDictionary::class));
        $this->assertEquals('bar', $value->getSubType());
    }

    public function testGettingTypeReturnsCorrectType(): void
    {
        $value = new ContentTypeHeaderValue('foo/bar', $this->createMock(IImmutableDictionary::class));
        $this->assertEquals('foo', $value->getType());
    }

    public function testTypeWithSuffixSetsTypeSubTypeAndSuffixesCorrectly(): void
    {
        $value = new ContentTypeHeaderValue('application/foo+json', $this->createMock(IImmutableDictionary::class));
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
        ];
    }

    /**
     * @dataProvider incorrectlyFormattedMediaTypeProvider
     */
    public function testIncorrectlyFormattedMediaTypeThrowsException($incorrectlyFormattedMediaType): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Media type must be in format {type}/{sub-type}, received {$incorrectlyFormattedMediaType}");
        new ContentTypeHeaderValue($incorrectlyFormattedMediaType, $this->createMock(IImmutableDictionary::class));
    }
}
