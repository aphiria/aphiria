<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections\Tests;

use Aphiria\Collections\KeyValuePair;
use Aphiria\Collections\KeyValuePairIterator;
use PHPUnit\Framework\TestCase;

class KeyValuePairIteratorTest extends TestCase
{
    public function testCurrentReturnsCurrentValueEvenWhenCallingNext(): void
    {
        $kvps = [
            new KeyValuePair('foo', 'bar'),
            new KeyValuePair('baz', 'quz')
        ];
        $iterator = new KeyValuePairIterator($kvps);
        $this->assertSame('bar', $iterator->current());
        $iterator->next();
        $this->assertSame('quz', $iterator->current());
    }
    public function testKeyReturnsCurrentKeyEvenWhenCallingNext(): void
    {
        $kvps = [
            new KeyValuePair('foo', 'bar'),
            new KeyValuePair('baz', 'quz')
        ];
        $iterator = new KeyValuePairIterator($kvps);
        $this->assertSame('foo', $iterator->key());
        $iterator->next();
        $this->assertSame('baz', $iterator->key());
    }

    public function testRewindResetsIterator(): void
    {
        $kvps = [
            new KeyValuePair('foo', 'bar'),
            new KeyValuePair('baz', 'quz')
        ];
        $iterator = new KeyValuePairIterator($kvps);
        $this->assertSame('foo', $iterator->key());
        $this->assertSame('bar', $iterator->current());
        $iterator->next();
        $this->assertSame('baz', $iterator->key());
        $this->assertSame('quz', $iterator->current());
        $iterator->rewind();
        $this->assertSame('foo', $iterator->key());
        $this->assertSame('bar', $iterator->current());
        $iterator->next();
        $this->assertSame('baz', $iterator->key());
        $this->assertSame('quz', $iterator->current());
    }

    public function testValidIsOnlyTrueWhileIteratorIsInBounds(): void
    {
        $kvps = [
            new KeyValuePair('foo', 'bar'),
            new KeyValuePair('baz', 'quz')
        ];
        $iterator = new KeyValuePairIterator($kvps);
        $this->assertTrue($iterator->valid());
        $iterator->next();
        $this->assertTrue($iterator->valid());
        $iterator->next();
        $this->assertFalse($iterator->valid());
    }
}
