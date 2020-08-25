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

use Aphiria\Collections\KeyValuePair;
use PHPUnit\Framework\TestCase;

class KeyValuePairTest extends TestCase
{
    public function testGettingKey(): void
    {
        $kvp = new KeyValuePair('foo', 'bar');
        $this->assertSame('foo', $kvp->getKey());
    }

    public function testGettingValue(): void
    {
        $kvp = new KeyValuePair('foo', 'bar');
        $this->assertSame('bar', $kvp->getValue());
    }
}
