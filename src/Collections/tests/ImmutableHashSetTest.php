<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections\Tests;

use Aphiria\Collections\ImmutableHashSet;
use Aphiria\Collections\Tests\Mocks\FakeObject;
use PHPUnit\Framework\TestCase;

class ImmutableHashSetTest extends TestCase
{
    public function testAddingArrayValueIsAcceptable(): void
    {
        $array = ['foo'];
        $set = new ImmutableHashSet([$array]);
        $this->assertTrue($set->containsValue($array));
        $this->assertEquals([$array], $set->toArray());
    }

    public function testAddingPrimitiveValuesIsAcceptable(): void
    {
        $int = 1;
        $string = 'foo';
        $set = new ImmutableHashSet([$int, $string]);
        $this->assertTrue($set->containsValue($int));
        $this->assertTrue($set->containsValue($string));
        $this->assertEquals([$int, $string], $set->toArray());
    }

    public function testAddingResourceValuesIsAcceptable(): void
    {
        $resource = \fopen('php://temp', 'r+b');
        $set = new ImmutableHashSet([$resource]);
        $this->assertTrue($set->containsValue($resource));
        $this->assertEquals([$resource], $set->toArray());
    }

    public function testAddingValue(): void
    {
        $object = new FakeObject();
        $set = new ImmutableHashSet([$object]);
        $this->assertEquals([$object], $set->toArray());
    }

    public function testCheckingExistenceOfValueReturnsWhetherOrNotThatValueExists(): void
    {
        /** @var ImmutableHashSet<string> $setWithNoValues */
        $setWithNoValues = new ImmutableHashSet([]);
        $this->assertFalse($setWithNoValues->containsValue('foo'));
        $setWithStringValue = new ImmutableHashSet(['foo']);
        $this->assertTrue($setWithStringValue->containsValue('foo'));
        $object = new FakeObject();
        $setWithObjectValue = new ImmutableHashSet([$object]);
        $this->assertTrue($setWithObjectValue->containsValue($object));
    }

    public function testCountReturnsNumberOfUniqueValuesInSet(): void
    {
        $object1 = new FakeObject();
        $object2 = new FakeObject();
        $this->assertSame(0, (new ImmutableHashSet([]))->count());
        $setWithOneValue = new ImmutableHashSet([$object1]);
        $this->assertSame(1, $setWithOneValue->count());
        $setWithOneUniqueValue = new ImmutableHashSet([$object1, $object1]);
        $this->assertSame(1, $setWithOneUniqueValue->count());
        $setWithTwoalues = new ImmutableHashSet([$object1, $object2]);
        $this->assertSame(2, $setWithTwoalues->count());
    }

    /**
     * Tests iterating over the values returns the values - not the hash keys
     */
    public function testIteratingOverValuesReturnsValuesNotHashKeys(): void
    {
        $expectedValues = [
            new FakeObject(),
            new FakeObject()
        ];
        $set = new ImmutableHashSet($expectedValues);
        $actualValues = [];

        foreach ($set as $key => $value) {
            // Make sure the hash keys aren't returned by the iterator
            $this->assertIsInt($key);
            $actualValues[] = $value;
        }

        $this->assertEquals($expectedValues, $actualValues);
    }
}
