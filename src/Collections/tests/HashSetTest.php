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

use Aphiria\Collections\HashSet;
use Aphiria\Collections\Tests\Mocks\FakeObject;
use PHPUnit\Framework\TestCase;

class HashSetTest extends TestCase
{
    private HashSet $set;

    protected function setUp(): void
    {
        $this->set = new HashSet();
    }

    public function testAddingArrayValueIsAcceptable(): void
    {
        $array = ['foo'];
        $this->set->add($array);
        $this->assertTrue($this->set->containsValue($array));
        $this->assertEquals([$array], $this->set->toArray());
    }

    public function testAddingPrimitiveValuesIsAcceptable(): void
    {
        $int = 1;
        $string = 'foo';
        $this->set->add($int);
        $this->set->add($string);
        $this->assertTrue($this->set->containsValue($int));
        $this->assertTrue($this->set->containsValue($string));
        $this->assertEquals([$int, $string], $this->set->toArray());
    }

    public function testAddingResourceValuesIsAcceptable(): void
    {
        $resource = \fopen('php://temp', 'r+b');
        $this->set->add($resource);
        $this->assertTrue($this->set->containsValue($resource));
        $this->assertEquals([$resource], $this->set->toArray());
    }

    public function testAddingValue(): void
    {
        $object = new FakeObject();
        $this->set->add($object);
        $this->assertEquals([$object], $this->set->toArray());
    }

    public function testCheckingExistenceOfValueReturnsWhetherOrNotThatValueExists(): void
    {
        $this->assertFalse($this->set->containsValue('foo'));
        $this->set->add('foo');
        $this->assertTrue($this->set->containsValue('foo'));
        $object = new FakeObject();
        $this->assertFalse($this->set->containsValue($object));
        $this->set->add($object);
        $this->assertTrue($this->set->containsValue($object));
    }

    public function testClearingSetRemovesAllValues(): void
    {
        $this->set->add(new FakeObject());
        $this->set->clear();
        $this->assertEquals([], $this->set->toArray());
    }

    public function testCountReturnsNumberOfUniqueValuesInSet(): void
    {
        $object1 = new FakeObject();
        $object2 = new FakeObject();
        $this->assertSame(0, $this->set->count());
        $this->set->add($object1);
        $this->assertSame(1, $this->set->count());
        $this->set->add($object1);
        $this->assertSame(1, $this->set->count());
        $this->set->add($object2);
        $this->assertSame(2, $this->set->count());
    }

    public function testEqualButNotSameObjectsAreNotIntersected(): void
    {
        $object1 = new FakeObject();
        $object2 = clone $object1;
        $this->set->add($object1);
        $newSet = $this->set->intersect([$object2]);
        $this->assertEquals([], $newSet->toArray());
    }

    public function testIntersectingDoesNotChangeOriginalSet(): void
    {
        $this->set->addRange(['foo', 'bar']);
        $this->set->intersect(['bar']);
        $this->assertEquals(['foo', 'bar'], $this->set->toArray());
    }

    public function testIntersectingIntersectsValuesOfSetAndArray(): void
    {
        $object1 = new FakeObject();
        $object2 = new FakeObject();
        $this->set->add($object1);
        $this->set->add($object2);
        $newSet = $this->set->intersect([$object2]);
        $this->assertEquals([$object2], $newSet->toArray());
    }

    public function testIteratingOverValuesReturnsValuesNotHashKeys(): void
    {
        $expectedValues = [
            new FakeObject(),
            new FakeObject()
        ];
        $this->set->addRange($expectedValues);
        $actualValues = [];

        foreach ($this->set as $key => $value) {
            // Make sure the hash keys aren't returned by the iterator
            $this->assertIsInt($key);
            $actualValues[] = $value;
        }

        $this->assertEquals($expectedValues, $actualValues);
    }

    public function testRemovingValue(): void
    {
        $object = new FakeObject();
        $this->set->add($object);
        $this->set->removeValue($object);
        $this->assertEquals([], $this->set->toArray());
    }

    public function testSorting(): void
    {
        $comparer = fn (string $a, string $b): int => $a === 'foo' ? 1 : -1;
        $this->set->add('foo');
        $this->set->add('bar');
        $newSet = $this->set->sort($comparer);
        $this->assertEquals(['bar', 'foo'], $newSet->toArray());
    }

    public function testSortingDoesNotChangeOriginalSet(): void
    {
        $comparer = fn (string $a, string $b): int => $a === 'foo' ? 1 : -1;
        $this->set->addRange(['foo', 'bar']);
        $this->set->sort($comparer);
        $this->assertEquals(['foo', 'bar'], $this->set->toArray());
    }

    public function testUnioningDoesNotChangeOriginalSet(): void
    {
        $this->set->add('foo');
        $this->set->union(['bar']);
        $this->assertEquals(['foo'], $this->set->toArray());
    }

    public function testUnionUnionsValuesOfSetAndArray(): void
    {
        $object = new FakeObject();
        $this->set->add($object);
        $newSet = $this->set->union(['bar', 'baz']);
        $this->assertEquals([$object, 'bar', 'baz'], $newSet->toArray());
    }
}
