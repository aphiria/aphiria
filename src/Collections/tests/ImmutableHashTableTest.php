<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections\Tests;

use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Collections\Tests\Mocks\FakeObject;
use InvalidArgumentException;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ImmutableHashTableTest extends TestCase
{
    public function testArrayAccessReturnsValuesAtKeys(): void
    {
        $hashTable = new ImmutableHashTable([new KeyValuePair('foo', 'bar')]);
        $this->assertSame('bar', $hashTable['foo']);
    }

    public function testContainsKey(): void
    {
        $hashTable = new ImmutableHashTable([new KeyValuePair('foo', 'bar')]);
        $this->assertFalse($hashTable->containsKey('baz'));
        $this->assertTrue($hashTable->containsKey('foo'));
    }

    public function testContainsKeyReturnsTrueEvenIfValuesIsNull(): void
    {
        $hashTable = new ImmutableHashTable([new KeyValuePair('foo', null)]);
        $this->assertTrue($hashTable->containsKey('foo'));
    }

    public function testContainsValue(): void
    {
        $hashTable = new ImmutableHashTable([new KeyValuePair('foo', 'bar')]);
        $this->assertFalse($hashTable->containsValue('baz'));
        $this->assertTrue($hashTable->containsValue('bar'));
    }

    public function testCount(): void
    {
        $hashTable = new ImmutableHashTable([new KeyValuePair('foo', 'bar'), new KeyValuePair('baz', 'blah')]);
        $this->assertSame(2, $hashTable->count());
    }

    public function testGetting(): void
    {
        $hashTable = new ImmutableHashTable([new KeyValuePair('foo', 'bar')]);
        $this->assertSame('bar', $hashTable->get('foo'));
    }

    public function testGettingAbsentVariableThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $hashTable = new ImmutableHashTable([]);
        $hashTable->get('does not exist');
    }

    public function testGettingKeysReturnsOriginalKeysNotHashKeys(): void
    {
        $kvp1 = new KeyValuePair(new FakeObject(), 'foo');
        $kvp2 = new KeyValuePair(new FakeObject(), 'bar');
        $hashTable = new ImmutableHashTable([$kvp1, $kvp2]);
        $this->assertEquals([$kvp1->getKey(), $kvp2->getKey()], $hashTable->getKeys());
    }

    public function testGettingValuesReturnsListOfValues(): void
    {
        $kvp1 = new KeyValuePair('foo', 'bar');
        $kvp2 = new KeyValuePair('baz', 'blah');
        $hashTable = new ImmutableHashTable([$kvp1, $kvp2]);
        $this->assertEquals([$kvp1->getValue(), $kvp2->getValue()], $hashTable->getValues());
    }

    public function testIssetReturnsWhetherOrNotKeyIsSet(): void
    {
        $hashTable = new ImmutableHashTable([new KeyValuePair('foo', 'bar')]);
        $this->assertTrue(isset($hashTable['foo']));
        $this->assertFalse(isset($hashTable['baz']));
    }

    public function testIteratingOverValues(): void
    {
        $expectedArray = [
            new KeyValuePair('foo', 'bar'),
            new KeyValuePair('baz', 'blah')
        ];
        $hashTable = new ImmutableHashTable($expectedArray);
        $actualValues = [];

        foreach ($hashTable as $key => $value) {
            // Make sure the hash keys aren't returned by the iterator
            $this->assertIsInt($key);
            $actualValues[] = $value;
        }

        $this->assertEquals($expectedArray, $actualValues);
    }

    public function testNonKeyValuePairInConstructorThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ImmutableHashTable(['foo' => 'bar']);
    }

    public function testSettingValueThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $hashTable = new ImmutableHashTable([]);
        $hashTable['foo'] = 'bar';
    }

    public function testToArray(): void
    {
        $hashTable = new ImmutableHashTable([new KeyValuePair('foo', 'bar'), new KeyValuePair('baz', 'blah')]);
        $expectedArray = [
            new KeyValuePair('foo', 'bar'),
            new KeyValuePair('baz', 'blah')
        ];
        $this->assertEquals($expectedArray, $hashTable->toArray());
    }

    public function testTryGetReturnsTrueIfKeyExistsAndFalseIfValueDoesNotExist(): void
    {
        $hashTable = new ImmutableHashTable([new KeyValuePair('foo', 'bar')]);
        $value = null;
        $this->assertFalse($hashTable->tryGet('baz', $value));
        $this->assertNull($value);
        $this->assertTrue($hashTable->tryGet('foo', $value));
        $this->assertSame('bar', $value);
    }

    public function testUnsettingValueThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $hashTable = new ImmutableHashTable([]);
        unset($hashTable['foo']);
    }
}
