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
 * Defines the interface for data contract converters to implement
 */
interface IDataContractConverter
{
    /**
     * Converts a data contract to a model
     *
     * @param string $type The type to convert to
     * @param mixed $dataContract The data contract to convert
     * @return object An instance of the input type
     * @throws InvalidArgumentException Thrown if no data contract converter exists for the input type
     */
    public function convertFromDataContract(string $type, $dataContract);

    /**
     * Converts a model to a data contract (the encodable representation of the model)
     *
     * @param object $object The model to convert
     * @return mixed The data contract
     * @throws InvalidArgumentException Thrown if no data contract converter exists for the input model
     */
    public function convertToDataContract($object);
}
