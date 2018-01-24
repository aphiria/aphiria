<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting;

use InvalidArgumentException;
use Opulence\Collections\IImmutableDictionary;
use Opulence\Collections\ImmutableHashTable;
use Opulence\Collections\KeyValuePair;
use Opulence\Net\Http\Headers\MediaTypeHeaderValue;

/**
 * Tests the media type header value
 */
class MediaTypeHeaderValueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Tests that getting the charset returns the one set in the constructor
     */
    public function testGettingCharsetReturnsOneSetInConstructor() : void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $value = new MediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertEquals('utf-8', $value->getCharset());
    }

    /**
     * Tests that getting the media type returns the one set in the constructor
     */
    public function testGettingMediaTypeReturnsOneSetInConstructor() : void
    {
        $parameters = new ImmutableHashTable([new KeyValuePair('charset', 'utf-8')]);
        $value = new MediaTypeHeaderValue('foo/bar', $parameters);
        $this->assertEquals('foo/bar', $value->getMediaType());
    }

    /**
     * Tests that getting the sub-type returns the correct sub-type
     */
    public function testGettingSubTypeReturnsCorrectSubtType() : void
    {
        $value = new MediaTypeHeaderValue('foo/bar', $this->createMock(IImmutableDictionary::class));
        $this->assertEquals('bar', $value->getSubType());
    }

    /**
     * Tests that getting the type returns the correct type
     */
    public function testGettingTypeReturnsCorrectType() : void
    {
        $value = new MediaTypeHeaderValue('foo/bar', $this->createMock(IImmutableDictionary::class));
        $this->assertEquals('foo', $value->getType());
    }

    /**
     * Tests that an incorrectly formatted media type throws an exception
     */
    public function testIncorrectlyFormattedMediaTypeThrowsException() : void
    {
        try {
            new MediaTypeHeaderValue('foo', $this->createMock(IImmutableDictionary::class));
            $this->fail('"foo" is in invalid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            new MediaTypeHeaderValue('foo/', $this->createMock(IImmutableDictionary::class));
            $this->fail('"foo/" is in invalid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }

        try {
            new MediaTypeHeaderValue('/foo', $this->createMock(IImmutableDictionary::class));
            $this->fail('"/foo" is in invalid media type');
        } catch (InvalidArgumentException $ex) {
            $this->assertTrue(true);
        }
    }
}
