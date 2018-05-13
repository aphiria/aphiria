<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Serialization;

use Opulence\Net\Http\Formatting\Serialization\ArrayProperty;

/**
 * Tests an array property
 */
class ArrayPropertyTest extends \PHPUnit\Framework\TestCase
{
    public function testIsArrayOfTypeIsAlwaysTrue(): void
    {
        $property = new ArrayProperty('foo', 'string', function () {
            // Don't do anything
        });
        $this->assertTrue($property->isArrayOfType());
    }
}
