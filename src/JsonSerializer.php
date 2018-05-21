<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization;

use Opulence\Serialization\Encoding\EncodingException;
use Opulence\Serialization\Encoding\IEncoder;

/**
 * Defines a JSON serializer
 */
class JsonSerializer implements ISerializer
{
    /** @var IEncoder The encoder to use to encode/decode values */
    private $encoder;

    /**
     * @param IEncoder $encoder The encoder to use to encode/decode values
     */
    public function __construct(IEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @inheritdoc
     */
    public function deserialize(string $value, string $type)
    {
        $encodedValue = json_decode($value, true);

        if (($jsonErrorCode = json_last_error()) !== JSON_ERROR_NONE) {
            throw new SerializationException('Failed to deserialize value: ' . json_last_error_msg());
        }

        try {
            return $this->encoder->decode($encodedValue, $type);
        } catch (EncodingException $ex) {
            throw new SerializationException('Failed to deserialize value', 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        try {
            $encodedValue = $this->encoder->encode($value);
        } catch (EncodingException $ex) {
            throw new SerializationException('Failed to serialize value', 0, $ex);
        }

        if (!($serializedValue = json_encode($encodedValue))) {
            throw new SerializationException('Failed to serialize value: ' . json_last_error_msg());
        }

        return $serializedValue;
    }
}
