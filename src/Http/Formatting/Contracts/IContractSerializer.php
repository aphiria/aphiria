<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Contracts;

use InvalidArgumentException;

/**
 * Defines the interface for contract serializers to implement
 */
interface IContractSerializer
{
    /**
     * Deserializes a contract to a specific type of contract
     * In a better world, this would be handled with a generic method
     *
     * @param string $serializedContract The serialized contract to deserialize
     * @param string $contractType The type of contract to deserialize to
     * @return IContract The deserialized contract
     * @throws InvalidArgumentException Thrown if the input contract type didn't implement IContract
     * @throws SerializationException Thrown if there was an error trying to deserialize to the input contract
     */
    public function deserializeContract(string $serializedContract, string $contractType): IContract;

    /**
     * Serializes a contract
     *
     * @param IContract $contract The contract to serialize
     * @return string The serialized contract
     * @throws SerializationException Thrown if there was an error serializing the input contract
     */
    public function serializeContract(IContract $contract): string;
}
