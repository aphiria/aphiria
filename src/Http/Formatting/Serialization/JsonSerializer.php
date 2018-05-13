<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

use Opulence\Net\Http\Formatting\Serialization\Encoding\EncodingException;
use OutOfBoundsException;

/**
 * Defines a JSON serializer
 */
class JsonSerializer extends Serializer
{
    /**
     * @inheritdoc
     */
    public function deserialize(string $value, string $type, bool $isArrayOfType = false)
    {
        $encodedValue = json_decode($value, true);

        if (($jsonErrorCode = json_last_error()) !== JSON_ERROR_NONE) {
            throw new SerializationException('Failed to deserialize value: ' . json_last_error_msg());
        }

        try {
            return $this->decodeValue($encodedValue, $type, $isArrayOfType);
        } catch (EncodingException | OutOfBoundsException $ex) {
            throw new SerializationException('Failed to deserialize value', 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        try {
            $encodedValue = $this->encodeValue($value);
        } catch (EncodingException | OutOfBoundsException $ex) {
            throw new SerializationException('Failed to serialize value', 0, $ex);
        }

        if (!($jsonEncodedContract = json_encode($encodedValue))) {
            throw new SerializationException('Failed to serialize value: ' . json_last_error_msg());
        }

        return $jsonEncodedContract;
    }
}
