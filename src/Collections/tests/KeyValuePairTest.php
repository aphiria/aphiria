<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections\Tests;

use Aphiria\Collections\KeyValuePair;
use PHPUnit\Framework\TestCase;

/**
 * Tests the key-value pair
 */
class KeyValuePairTest extends TestCase
{
    public function testGettingKey(): void
    {
        $kvp = new KeyValuePair('foo', 'bar');
        $this->assertEquals('foo', $kvp->getKey());
    }

    public function testGettingValue(): void
    {
        $kvp = new KeyValuePair('foo', 'bar');
        $this->assertEquals('bar', $kvp->getValue());
    }
}
