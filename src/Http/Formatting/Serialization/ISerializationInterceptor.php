<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

/**
 * Defines the interface for serialization interceptors to implement
 */
interface ISerializationInterceptor
{
    /**
     * Provides a hook for deserializing data
     *
     * @param mixed $contract The deserialized contract
     * @param string $type The type that was deserialized
     * @return mixed The deserialized contract
     */
    public function onDeserialization($contract, string $type);

    /**
     * Provides a hook for serializing a contract
     *
     * @param mixed $contract The contract being serialized
     * @return mixed The modified contract value
     */
    public function onSerialization($contract);
}
