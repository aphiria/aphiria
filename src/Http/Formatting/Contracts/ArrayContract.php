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
 * Defines a contract that is an array of data
 */
class ArrayContract
{
    /** @var array The list of values in the contract */
    private $values;

    /**
     * @param array $values The list of values in the contract
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Gets the list of values in the contract
     *
     * @return array The list of values in the contract
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
