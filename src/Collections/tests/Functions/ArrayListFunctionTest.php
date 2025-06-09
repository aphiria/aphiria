<?php

namespace Aphiria\Collections\Tests\Functions;

use Aphiria\Collections\ArrayList;
use PHPUnit\Framework\TestCase;
use function Aphiria\Collections\Functions\array_list;

class ArrayListFunctionTest extends TestCase
{
    public function testArrayListFunctionCreatesArrayListWithValues(): void
    {
        $values = ['foo', 'bar'];
        $list = array_list($values);
        $this->assertInstanceOf(ArrayList::class, $list);
        $this->assertEquals($values, $list->toArray());
    }

    public function testArrayListFunctionCreatesEmptyArrayListWhenNoValuesProvided(): void
    {
        $list = array_list();
        $this->assertInstanceOf(ArrayList::class, $list);
        $this->assertCount(0, $list);
    }

    public function testArrayListFunctionMaintainsTypeSafety(): void
    {
        $values = [1, 2, 3];
        $list = array_list($values);
        $this->assertSame($values, $list->toArray());
        $this->assertSame(1, $list->get(0));
    }

    public function testArrayListFunctionWorksWithMixedTypes(): void
    {
        $values = [1, 'foo', true];
        $list = array_list($values);
        $this->assertEquals($values, $list->toArray());
    }

    public function testArrayListFunctionReturnsNewInstanceEachCall(): void
    {
        $list1 = array_list(['foo']);
        $list2 = array_list(['bar']);
        $this->assertEquals(['foo'], $list1->toArray());
        $this->assertEquals(['bar'], $list2->toArray());
        $this->assertNotSame($list1, $list2);
    }
}
