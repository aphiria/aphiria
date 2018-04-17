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
class ArrayContract implements IContract
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
     * @inheritdoc
     */
    public function getValue(): array
    {
        return $this->values;
    }
}
