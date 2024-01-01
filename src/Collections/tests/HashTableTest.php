<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections\Tests;

use Aphiria\Collections\HashTable;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Collections\Tests\Mocks\FakeObject;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class HashTableTest extends TestCase
{
    private HashTable $hashTable;

    protected function setUp(): void
    {
        $this->hashTable = new HashTable();
    }

    public function testAddingRangeMakesEachValueRetrievable(): void
    {
        $this->hashTable->addRange([new KeyValuePair('foo', 'bar'), new KeyValuePair('baz', 'blah')]);
        $this->assertSame('bar', $this->hashTable->get('foo'));
        $this->assertSame('blah', $this->hashTable->get('baz'));
    }

    public function testAddingValueMakesItRetrievable(): void
    {
        $this->hashTable->add('foo', 'bar');
        $this->assertSame('bar', $this->hashTable->get('foo'));
    }

    public function testCheckingOffsetExists(): void
    {
        $this->hashTable['foo'] = 'bar';
        $this->assertTrue(isset($this->hashTable['foo']));
    }

    public function testClearing(): void
    {
        $this->hashTable->add('foo', 'bar');
        $this->hashTable->clear();
        $this->assertEquals([], $this->hashTable->toArray());
    }

    public function testContainsKey(): void
    {
        $this->assertFalse($this->hashTable->containsKey('foo'));
        $this->hashTable->add('foo', 'bar');
        $this->assertTrue($this->hashTable->containsKey('foo'));
    }

    public function testContainsKeyReturnsTrueEvenIfValuesIsNull(): void
    {
        $this->hashTable->add('foo', null);
        $this->assertTrue($this->hashTable->containsKey('foo'));
    }

    public function testContainsValue(): void
    {
        $this->assertFalse($this->hashTable->containsValue('bar'));
        $this->hashTable->add('foo', 'bar');
        $this->assertTrue($this->hashTable->containsValue('bar'));
    }

    public function testCount(): void
    {
        $this->hashTable->add('foo', 'bar');
        $this->assertSame(1, $this->hashTable->count());
        $this->hashTable->add('bar', 'foo');
        $this->assertSame(2, $this->hashTable->count());
    }

    public function testGetting(): void
    {
        $this->hashTable->add('foo', 'bar');
        $this->assertSame('bar', $this->hashTable->get('foo'));
    }

    public function testGettingAbsentVariableThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->hashTable->get('does not exist');
    }

    public function testGettingAsArray(): void
    {
        $this->hashTable->add('foo', 'bar');
        $this->assertSame('bar', $this->hashTable['foo']);
    }

    /**
     * Tests that getting the keys returns the original keys, not the hash keys
     */
    public function testGettingKeysReturnsOriginalKeysNotHashKeys(): void
    {
        $key1 = new FakeObject();
        $key2 = new FakeObject();
        $this->hashTable->add($key1, 'foo');
        $this->hashTable->add($key2, 'bar');
        $this->assertEquals([$key1, $key2], $this->hashTable->getKeys());
    }

    public function testGettingValuesReturnsListOfValues(): void
    {
        $this->hashTable->add('foo', 'bar');
        $this->hashTable->add('baz', 'blah');
        $this->assertEquals(['bar', 'blah'], $this->hashTable->getValues());
    }

    public function testIteratingOverValues(): void
    {
        $this->hashTable->add('foo', 'bar');
        $this->hashTable->add('baz', 'blah');
        /** @var list<array{0: string, 1: string}> $expectedValues */
        $expectedValues = [['foo', 'bar'], ['baz', 'blah']];
        $expectedValuesIndex = 0;

        foreach ($this->hashTable as $key => $value) {
            $this->assertSame($expectedValues[$expectedValuesIndex][0], $key);
            $this->assertSame($expectedValues[$expectedValuesIndex][1], $value);
            $expectedValuesIndex++;
        }
    }

    /**
     * Tests that a non-key-value pair in the add-range method throws an exception
     */
    public function testNonKeyValuePairInAddRangeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress ArgumentTypeCoercion Purposely testing passing invalid values */
        $this->hashTable->addRange(['foo' => 'bar']);
    }

    /**
     * Tests that a non-key-value pair in the constructor throws an exception
     */
    public function testNonKeyValuePairInConstructorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @psalm-suppress ArgumentTypeCoercion Purposely testing passing invalid values */
        new HashTable(['foo' => 'bar']);
    }

    public function testPassingParametersInConstructor(): void
    {
        $hashTable = new HashTable([new KeyValuePair('foo', 'bar'), new KeyValuePair('baz', 'blah')]);
        $this->assertSame('bar', $hashTable->get('foo'));
        $this->assertSame('blah', $hashTable->get('baz'));
    }

    public function testRemoveKey(): void
    {
        $this->hashTable->add('foo', 'bar');
        $this->hashTable->removeKey('foo');
        $this->assertFalse($this->hashTable->containsKey('foo'));
    }

    public function testSettingItem(): void
    {
        $this->hashTable['foo'] = 'bar';
        $this->assertSame('bar', $this->hashTable['foo']);
    }

    public function testToArray(): void
    {
        $this->hashTable->add('foo', 'bar');
        $this->hashTable->add('baz', 'blah');
        $expectedArray = [
            new KeyValuePair('foo', 'bar'),
            new KeyValuePair('baz', 'blah')
        ];
        $this->assertEquals($expectedArray, $this->hashTable->toArray());
    }

    /**
     * Tests the trying to get a value returns true if the key exists and false if it doesn't
     */
    public function testTryGetReturnsTrueIfKeyExistsAndFalseIfValueDoesNotExist(): void
    {
        $value = null;
        $this->assertFalse($this->hashTable->tryGet('foo', $value));
        $this->assertNull($value);
        $this->hashTable->add('foo', 'bar');
        $this->assertTrue($this->hashTable->tryGet('foo', $value));
        /** @psalm-suppress DocblockTypeContradiction The hash table is of type <string, string>, not <string, null> - bug */
        $this->assertSame('bar', $value);
    }

    public function testUnsetting(): void
    {
        $this->hashTable['foo'] = 'bar';
        unset($this->hashTable['foo']);
        $this->assertFalse($this->hashTable->containsKey('foo'));
    }
}
