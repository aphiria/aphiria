<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Tests\Http\Formatting\Contracts;

use Opulence\Net\Http\Formatting\Contracts\DictionaryContract;

/**
 * Tests the dictionary contract
 */
class DictionaryContractTest extends \PHPUnit\Framework\TestCase
{
    /** @var DictionaryContract The contract to use in tests */
    private $contract;

    public function setUp(): void
    {
        $this->contract = new DictionaryContract(['foo' => 'bar', 'baz' => 'blah']);
    }

    public function testGettingValueReturnsSameValueFromConstructor(): void
    {
        $expectedValues = ['foo' => 'bar', 'baz' => 'blah'];
        $contract = new DictionaryContract($expectedValues);
        $this->assertEquals($expectedValues, $contract->getValue());
    }
}
