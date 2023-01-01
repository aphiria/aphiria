<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections\Tests;

use Aphiria\Collections\KeyHasher;
use Aphiria\Collections\Tests\Mocks\SerializableObject;
use Aphiria\Collections\Tests\Mocks\UnserializableObject;
use PHPUnit\Framework\TestCase;

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
        $this->assertSame('__aphiria:a:' . \md5(\serialize($array)), $this->keyHasher->getHashKey($array));
    }

    public function testNullCanBeHashed(): void
    {
        $this->assertSame('__aphiria:u', $this->keyHasher->getHashKey(null));
    }

    public function testScalarsAreHashedToCorrectKey(): void
    {
        $this->assertSame('__aphiria:s:1', $this->keyHasher->getHashKey('1'));
        $this->assertSame('__aphiria:i:1', $this->keyHasher->getHashKey(1));
        $this->assertSame('__aphiria:f:1.1', $this->keyHasher->getHashKey(1.1));
    }

    public function testResourceIsHashedUsingItsStringValue(): void
    {
        $resource = \fopen('php://temp', 'r+b');
        $this->assertSame("__aphiria:r:$resource", $this->keyHasher->getHashKey($resource));
    }

    /**
     * Tests that a serializable object is hashed with its __toString() method
     */
    public function testSerializableObjectIsHashedWithToStringMethod(): void
    {
        $object = new SerializableObject('foo');
        $this->assertSame('__aphiria:so:foo', $this->keyHasher->getHashKey($object));
    }

    public function testUnserializableObjectIsHashedWithObjectHash(): void
    {
        $object = new UnserializableObject();
        $this->assertSame('__aphiria:o:' . \spl_object_hash($object), $this->keyHasher->getHashKey($object));
    }
}
