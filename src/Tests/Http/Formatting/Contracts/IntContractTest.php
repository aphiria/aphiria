<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Contracts;

use Opulence\Net\Http\Formatting\Contracts\IntContract;

/**
 * Tests the integer contract
 */
class IntContractTest extends \PHPUnit\Framework\TestCase
{
    public function testGettingTheValueReturnsSameValueSetInConstructor(): void
    {
        $contract = new IntContract(1);
        $this->assertEquals(1, $contract->getValue());
    }
}
