<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections\Tests;

use Aphiria\Collections\KeyValuePair;
use PHPUnit\Framework\TestCase;
use function Aphiria\Collections\Functions\array_list;
use function Aphiria\Collections\Functions\hash_set;
use function Aphiria\Collections\Functions\hash_table;
use function Aphiria\Collections\Functions\immutable_array_list;
use function Aphiria\Collections\Functions\immutable_hash_set;
use function Aphiria\Collections\Functions\immutable_hash_table;
use function Aphiria\Collections\Functions\queue;
use function Aphiria\Collections\Functions\stack;

class FunctionsTest extends TestCase
{
    public function testArrayListFunctionCreatesArrayListWithValues(): void
    {
        $values = ['foo', 'bar'];
        $list = array_list($values);
        $this->assertEquals($values, $list->toArray());
    }

    public function testArrayListFunctionCreatesEmptyArrayListWhenNoValuesProvided(): void
    {
        $this->assertCount(0, array_list());
    }

    public function testArrayListFunctionReturnsNewInstanceEachCall(): void
    {
        $this->assertNotSame(array_list(['foo']), array_list(['foo']));
    }

    public function testHashSetFunctionCreatesHashSetWithValues(): void
    {
        $values = ['foo', 'bar'];
        $hashSet = hash_set($values);
        $this->assertEquals($values, $hashSet->toArray());
    }

    public function testHashSetFunctionCreatesEmptyHashSetWhenNoValuesProvided(): void
    {
        $this->assertCount(0, hash_set());
    }

    public function testHashSetFunctionReturnsNewInstanceEachCall(): void
    {
        $this->assertNotSame(hash_set(['foo']), hash_set(['foo']));
    }

    public function testHashTableFunctionCreatesHashTableWithValues(): void
    {
        $kvps = [new KeyValuePair('foo', 'bar'),  new KeyValuePair('baz', 'blah')];
        $hashTable = hash_table($kvps);
        $this->assertEquals($kvps, $hashTable->toArray());
    }

    public function testHashTableFunctionCreatesEmptyHashTableWhenNoValuesProvided(): void
    {
        $this->assertCount(0, hash_table());
    }

    public function testHashTableFunctionReturnsNewInstanceEachCall(): void
    {
        $kvps = [new KeyValuePair('foo', 'bar')];
        $this->assertNotSame(hash_table($kvps), hash_table($kvps));
    }

    public function testImmutableArrayListFunctionCreatesImmutableArrayListWithValues(): void
    {
        $values = ['foo', 'bar'];
        $list = immutable_array_list($values);
        $this->assertEquals($values, $list->toArray());
    }

    public function testImmutableArrayListFunctionCreatesEmptyImmutableArrayListWhenNoValuesProvided(): void
    {
        $this->assertCount(0, immutable_array_list());
    }

    public function testImmutableArrayListFunctionReturnsNewInstanceEachCall(): void
    {
        $this->assertNotSame(immutable_array_list(['foo']), immutable_array_list(['foo']));
    }

    public function testImmutableHashSetFunctionCreatesImmutableHashSetWithValues(): void
    {
        $values = ['foo', 'bar'];
        $hashSet = immutable_hash_set($values);
        $this->assertEquals($values, $hashSet->toArray());
    }

    public function testImmutableHashSetFunctionCreatesEmptyImmutableHashSetWhenNoValuesProvided(): void
    {
        $this->assertCount(0, immutable_hash_set());
    }

    public function testImmutableHashSetFunctionReturnsNewInstanceEachCall(): void
    {
        $this->assertNotSame(immutable_hash_set(['foo']), immutable_hash_set(['foo']));
    }

    public function testImmutableHashTableFunctionCreatesHashTableWithValues(): void
    {
        $kvps = [new KeyValuePair('foo', 'bar'),  new KeyValuePair('baz', 'blah')];
        $hashTable = immutable_hash_table($kvps);
        $this->assertEquals($kvps, $hashTable->toArray());
    }

    public function testImmutableHashTableFunctionCreatesEmptyHashTableWhenNoValuesProvided(): void
    {
        $this->assertCount(0, immutable_hash_table());
    }

    public function testImmutableHashTableFunctionReturnsNewInstanceEachCall(): void
    {
        $kvps = [new KeyValuePair('foo', 'bar')];
        $this->assertNotSame(hash_table($kvps), hash_table($kvps));
    }

    public function testQueueFunctionCreatesQueueWithValues(): void
    {
        $queue = queue();
        $queue->enqueue('foo');
        $queue->enqueue('bar');
        $this->assertEquals(['foo', 'bar'], $queue->toArray());
    }

    public function testQueueFunctionReturnsNewInstanceEachCall(): void
    {
        $this->assertNotSame(queue(), queue());
    }

    public function testStackFunctionCreatesStackWithValues(): void
    {
        $stack = stack();
        $stack->push('foo');
        $stack->push('bar');
        $this->assertEquals(['bar', 'foo'], $stack->toArray());
    }

    public function testStackFunctionReturnsNewInstanceEachCall(): void
    {
        $this->assertNotSame(stack(), stack());
    }
}
