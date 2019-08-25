<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections\Tests;

use Aphiria\Collections\KeyHasher;
use Aphiria\Collections\Tests\Mocks\SerializableObject;
use Aphiria\Collections\Tests\Mocks\UnserializableObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests the key hasher
 */
class KeyHasherTest extends TestCase
{
    private KeyHasher $keyHasher;

    protected function setUp(): void
    {
        $this->keyHasher = new KeyHasher();
    }

    public function testArraysAreHashedToCorrectKey(): void
    {
        $array = ['foo'];
        $this->assertEquals('__aphiria:a:' . md5(serialize($array)), $this->keyHasher->getHashKey($array));
    }

    public function testScalarsAreHashedToCorrectKey(): void
    {
        $this->assertEquals('__aphiria:s:1', $this->keyHasher->getHashKey('1'));
        $this->assertEquals('__aphiria:i:1', $this->keyHasher->getHashKey(1));
        $this->assertEquals('__aphiria:f:1.1', $this->keyHasher->getHashKey(1.1));
    }

    public function testResourceIsHashedUsingItsStringValue(): void
    {
        $resource = fopen('php://temp', 'r+b');
        $this->assertEquals("__aphiria:r:$resource", $this->keyHasher->getHashKey($resource));
    }

    /**
     * Tests that a serializable object is hashed with its __toString() method
     */
    public function testSerializableObjectIsHashedWithToStringMethod(): void
    {
        $object = new SerializableObject('foo');
        $this->assertEquals('__aphiria:so:foo', $this->keyHasher->getHashKey($object));
    }

    public function testUnserializableObjectIsHashedWithObjectHash(): void
    {
        $object = new UnserializableObject();
        $this->assertEquals('__aphiria:o:' . spl_object_hash($object), $this->keyHasher->getHashKey($object));
    }
}
