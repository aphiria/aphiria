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
use Opulence\Net\Http\Headers\ContentTypeHeaderValue;

/**
 * Tests the Content-Type header value
 */
class ContentTypeHeaderValueTest extends \PHPUnit\Framework\TestCase
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

    public function testIncorrectlyFormattedMediaTypeThrowsException(): void
    {
        try {
            new ContentTypeHeaderValue('foo', $this->createMock(IImmutableDictionary::class));
            $this->fail('"foo" is in invalid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            new ContentTypeHeaderValue('foo/', $this->createMock(IImmutableDictionary::class));
            $this->fail('"foo/" is in invalid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            new ContentTypeHeaderValue('/foo', $this->createMock(IImmutableDictionary::class));
            $this->fail('"/foo" is in invalid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
    }
}
