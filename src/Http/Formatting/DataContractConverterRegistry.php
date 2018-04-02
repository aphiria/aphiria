<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting;

use InvalidArgumentException;

/**
 * Defines a data contract converter registry
 */
class DataContractConverterRegistry
{
    /** @var array The mapping of types to to/from converters */
    private $converters = [];

    /**
     * Gets the converter that maps a data contract to a model
     *
     * @param string $type The type whose converter we want
     * @return callable The data-contract-to-model converter
     * @throws InvalidArgumentException Thrown if no converter was found for the input type
     */
    public function getFromDataContractConverter(string $type): callable
    {
        if (!isset($this->converters[$type])) {
            throw new InvalidArgumentException("No data contract converter found for type $type");
        }

        return $this->converters[$type]['from'];
    }

    /**
     * Gets the converter that maps a model to a data contract
     *
     * @param string $type The type whose converter we want
     * @return callable The model-to-data-contract converter
     * @throws InvalidArgumentException Thrown if no converter was found for the input type
     */
    public function getToDataContractConverter(string $type): callable
    {
        if (!isset($this->converters[$type])) {
            throw new InvalidArgumentException("No data contract converter found for type $type");
        }

        return $this->converters[$type]['to'];
    }

    /**
     * Registers data contract converter for a particular type
     *
     * @param string $type The type whose converters we're registering
     * @param callable $toDataContractConverter The converter that converts a model to a data contract
     *      Note: Must accept the model and an instance of IDataContractConverter
     * @param callable $fromDataContractConverter The converter that converts a data contract to a model
     *      Note: Must accept the data contract and an instance of IDataContractConverter
     */
    public function registerDataContractConverter(
        string $type,
        callable $toDataContractConverter,
        callable $fromDataContractConverter
    ) : void {
        $this->converters[$type] = ['to' => $toDataContractConverter, 'from' => $fromDataContractConverter];
    }
}
