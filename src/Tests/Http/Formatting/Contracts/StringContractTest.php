<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Contracts;

use Opulence\Net\Http\Formatting\Contracts\StringContract;

/**
 * Tests the string contract
 */
class StringContractTest extends \PHPUnit\Framework\TestCase
{
    public function testGettingTheValueReturnsSameValueSetInConstructor(): void
    {
        $contract = new StringContract('foo');
        $this->assertEquals('foo', $contract->getValue());
    }
}
