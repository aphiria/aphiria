<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Contracts;

use Opulence\Net\Http\Formatting\Contracts\ArrayContract;

/**
 * Tests the array contract
 */
class ArrayContractTest extends \PHPUnit\Framework\TestCase
{
    public function testGettingValuesReturnsSameValuesInConstructor(): void
    {
        $expectedValues = ['foo', 'bar'];
        $contract = new ArrayContract($expectedValues);
        $this->assertSame($expectedValues, $contract->getValues());
    }
}
