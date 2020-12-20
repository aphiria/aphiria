<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections\Tests;

use Aphiria\Collections\Stack;
use PHPUnit\Framework\TestCase;

class StackTest extends TestCase
{
    private Stack $stack;

    protected function setUp(): void
    {
        $this->stack = new Stack();
    }

    public function testClearing(): void
    {
        $this->stack->push('foo');
        $this->stack->clear();
        $this->assertEquals([], $this->stack->toArray());
    }

    public function testContainsValueReturnsWhetherOrNotValueExists(): void
    {
        $this->assertFalse($this->stack->containsValue('foo'));
        $this->stack->push('foo');
        $this->assertTrue($this->stack->containsValue('foo'));
    }

    public function testCounting(): void
    {
        $this->assertSame(0, $this->stack->count());
        $this->stack->push('foo');
        $this->assertSame(1, $this->stack->count());
        $this->stack->push('bar');
        $this->assertSame(2, $this->stack->count());
    }

    public function testIteratingOverValues(): void
    {
        $this->stack->push('foo');
        $this->stack->push('bar');
        $actualValues = [];

        foreach ($this->stack as $value) {
            $actualValues[] = $value;
        }

        $this->assertEquals(['bar', 'foo'], $actualValues);
    }

    public function testPeekingWhenNoValuesInStackReturnsNull(): void
    {
        $this->assertNull($this->stack->peek());
    }

    public function testPeekReturnsTopValue(): void
    {
        $this->stack->push('foo');
        $this->assertSame('foo', $this->stack->peek());
        $this->stack->push('bar');
        $this->assertSame('bar', $this->stack->peek());
    }

    public function testPoppingRemovesValueFromTopOfStack(): void
    {
        $this->stack->push('foo');
        $this->stack->push('bar');
        $this->assertSame('bar', $this->stack->pop());
        $this->assertEquals(['foo'], $this->stack->toArray());
        $this->assertSame('foo', $this->stack->pop());
        $this->assertEquals([], $this->stack->toArray());
    }

    public function testPoppingWhenNoValuesAreInStackReturnsNull(): void
    {
        $this->assertNull($this->stack->pop());
    }

    public function testPushingAddsValueToTopOfStack(): void
    {
        $this->stack->push('foo');
        $this->stack->push('bar');
        $this->assertSame('bar', $this->stack->pop());
        $this->assertSame('foo', $this->stack->pop());
    }

    public function testToArrayConvertsTheStackToArray(): void
    {
        $this->stack->push('foo');
        $this->stack->push('bar');
        $this->assertEquals(['bar', 'foo'], $this->stack->toArray());
    }
}
