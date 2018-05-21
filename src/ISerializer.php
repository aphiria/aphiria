<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization;

/**
 * Defines the interface for serializers to implement
 */
interface ISerializer
{
    /**
     * Deserializes a value to an instance of the input type
     * In a better world, this would be handled with a generic method
     *
     * @param string $value The serialized value to deserialize
     * @param string $type The type of value to deserialize to (ending with '[]' if an array of $type)
     * @return mixed The deserialized value
     * @throws SerializationException Thrown if there was an error trying to deserialize to the input value
     */
    public function deserialize(string $value, string $type);

    /**
     * Serializes a value
     *
     * @param mixed $value The value to serialize
     * @return string The serialized value
     * @throws SerializationException Thrown if there was an error serializing the input value
     */
    public function serialize($value): string;
}
