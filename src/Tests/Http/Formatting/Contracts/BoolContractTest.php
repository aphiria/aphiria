<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Contracts;

use Opulence\Net\Http\Formatting\Contracts\BoolContract;

/**
 * Tests the boolean contract
 */
class BoolContractTest extends \PHPUnit\Framework\TestCase
{
    public function testGettingTheValueReturnsSameValueSetInConstructor(): void
    {
        $trueContract = new BoolContract(true);
        $this->assertTrue($trueContract->getValue());
        $falseContract = new BoolContract(false);
        $this->assertFalse($falseContract->getValue());
    }
}
