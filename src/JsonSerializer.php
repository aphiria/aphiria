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
 * Defines a JSON serializer
 */
class JsonSerializer extends Serializer
{
    /**
     * @inheritdoc
     */
    public function deserialize(string $value, string $type)
    {
        $encodedValue = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SerializationException('Failed to deserialize value: ' . json_last_error_msg());
        }

        return $this->decode($encodedValue, $type);
    }

    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        $encodedValue = $this->encode($value);

        if (($serializedValue = json_encode($encodedValue)) === false) {
            throw new SerializationException('Failed to serialize value: ' . json_last_error_msg());
        }

        return $serializedValue;
    }
}
