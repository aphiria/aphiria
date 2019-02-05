<?php

/*
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (c) 2019 David Young
 * @license   https://github.com/aphiria/serialization/blob/master/LICENSE.md
 */

namespace Aphiria\Serialization;

use JsonException;

/**
 * Defines a JSON serializer
 */
final class JsonSerializer extends Serializer
{
    /**
     * @inheritdoc
     */
    public function deserialize(string $value, string $type)
    {
        try {
            $encodedValue = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new SerializationException('Failed to deserialize value', 0, $ex);
        }

        return $this->decode($encodedValue, $type);
    }

    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        $encodedValue = $this->encode($value);

        try {
            $serializedValue = json_encode($encodedValue, JSON_THROW_ON_ERROR);
        } catch (JsonException $ex) {
            throw new SerializationException('Failed to serialize value', 0, $ex);
        }

        return $serializedValue;
    }
}
