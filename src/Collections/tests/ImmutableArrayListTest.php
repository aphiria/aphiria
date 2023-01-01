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

use Aphiria\Collections\ImmutableArrayList;
use OutOfRangeException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ImmutableArrayListTest extends TestCase
{
    public function testCheckingOffsetExists(): void
    {
        $arrayList = new ImmutableArrayList(['foo']);
        $this->assertTrue(isset($arrayList[0]));
    }

    public function testContainsValue(): void
    {
        $arrayList = new ImmutableArrayList(['foo']);
        $this->assertTrue($arrayList->containsValue('foo'));
        /** @psalm-suppress InvalidArgument We are explicitly checking a value that does not exist */
        $this->assertFalse($arrayList->containsValue('bar'));
    }

    public function tesContainsValueReturnsTrueEvenIfValuesIsNull(): void
    {
        $arrayList = new ImmutableArrayList([null]);
        $this->assertTrue($arrayList->containsValue(null));
    }

    public function testCount(): void
    {
        $arrayList = new ImmutableArrayList(['foo']);
        $this->assertSame(1, $arrayList->count());
        $arrayList = new ImmutableArrayList(['foo', 'bar']);
        $this->assertSame(2, $arrayList->count());
    }

    public function testGetting(): void
    {
        $arrayList = new ImmutableArrayList(['foo']);
        $this->assertSame('foo', $arrayList->get(0));
    }

    public function testGettingAsArray(): void
    {
        $arrayList = new ImmutableArrayList(['foo']);
        $this->assertSame('foo', $arrayList[0]);
    }

    public function testGettingIndexGreaterThanListLengthThrowsException(): void
    {
        $this->expectException(OutOfRangeException::class);
        $arrayList = new ImmutableArrayList(['foo']);
        $arrayList->get(1);
    }

    public function testGettingIndexLessThanZeroThrowsException(): void
    {
        $this->expectException(OutOfRangeException::class);
        $arrayList = new ImmutableArrayList(['foo']);
        $arrayList->get(-1);
    }

    public function testIteratingOverValues(): void
    {
        $arrayList = new ImmutableArrayList(['foo', 'bar']);
        $actualValues = [];

        foreach ($arrayList as $value) {
            $actualValues[] = $value;
        }

        $this->assertEquals(['foo', 'bar'], $actualValues);
    }

    public function testSettingValueThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $arrayList = new ImmutableArrayList([]);
        $arrayList[0] = 'foo';
    }

    public function testToArray(): void
    {
        $arrayList = new ImmutableArrayList(['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $arrayList->toArray());
    }

    public function testUnsettingValueThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $arrayList = new ImmutableArrayList(['foo']);
        unset($arrayList[0]);
    }
}
