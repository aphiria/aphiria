<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Serialization;

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
