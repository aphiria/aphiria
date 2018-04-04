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
use OutOfBoundsException;

/**
 * Tests the dictionary contract
 */
class DictionaryContractTest extends \PHPUnit\Framework\TestCase
{
    /** @var DictionaryContract The contract to use in tests */
    private $contract;

    /**
     * Sets up the tests
     */
    public function setUp(): void
    {
        $this->contract = new DictionaryContract(['foo' => 'bar', 'baz' => 'blah']);
    }

    public function testGettingPropertyValueThatDoesNotExistThrowsException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->contract->getPropertyValue('does not exist');
    }

    public function testGettingPropertyValueThatExistsReturnsTheValue(): void
    {
        $this->assertEquals('bar', $this->contract->getPropertyValue('foo'));
        $this->assertEquals('blah', $this->contract->getPropertyValue('baz'));
    }

    public function testGettingValuesReturnsSameValuesSetInConstructor(): void
    {
        $this->assertEquals(['foo' => 'bar', 'baz' => 'blah'], $this->contract->getValues());
    }
}
