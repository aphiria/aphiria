<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Contracts;

use OutOfBoundsException;

/**
 * Defines a contract that is a dictionary of data
 */
class DictionaryContract
{
    /** @var array The dictionary values in the contract */
    private $values;

    /**
     * @param array $values The dictionary values in the contract
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * Gets the value of a certain property
     *
     * @param string $propertyName The name of the property whose value we want
     * @return mixed The value of the property
     * @throws OutOfBoundsException Thrown if the property did not exist
     */
    public function getPropertyValue(string $propertyName)
    {
        if (!array_key_exists($propertyName, $this->values)) {
            throw new OutOfBoundsException("Property \"$propertyName\" does not exist");
        }

        return $this->values[$propertyName];
    }

    /**
     * Gets the dictionary values in the contract
     *
     * @return array The dictionary values in the contract
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
