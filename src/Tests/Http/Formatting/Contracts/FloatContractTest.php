<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Contracts;

use Opulence\Net\Http\Formatting\Contracts\FloatContract;

/**
 * Tests the float contract
 */
class FloatContractTest extends \PHPUnit\Framework\TestCase
{
    public function testGettingTheValueReturnsSameValueSetInConstructor(): void
    {
        $contract = new FloatContract(1.5);
        $this->assertEquals(1.5, $contract->getValue());
    }
}
