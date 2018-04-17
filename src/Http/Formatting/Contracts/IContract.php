<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Contracts;

/**
 * Defines the interface for contracts to implement
 */
interface IContract
{
    /**
     * Gets the value of the contract
     *
     * @return mixed The contract's value
     */
    public function getValue();
}
