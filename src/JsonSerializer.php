<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization;

use InvalidArgumentException;
use Opulence\Serialization\Encoding\DefaultEncoderRegistrant;
use Opulence\Serialization\Encoding\EncoderRegistry;
use Opulence\Serialization\Encoding\EncodingContext;
use Opulence\Serialization\Encoding\EncodingException;
use OutOfBoundsException;

/**
 * Defines a JSON serializer
 */
class JsonSerializer implements ISerializer
{
    /** @var EncoderRegistry The encoder registry to use to encode/decode values */
    private $encoders;

    /**
     * @param EncoderRegistry|null $encoders The encoder registry to use to encode/decode values
     */
    public function __construct(EncoderRegistry $encoders = null)
    {
        $this->encoders = $encoders ?? (new DefaultEncoderRegistrant)->registerDefaultEncoders(new EncoderRegistry);
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
            return $this->encoders->getEncoderForType($type)
                ->decode($encodedValue, $type, new EncodingContext());
        } catch (EncodingException | InvalidArgumentException | OutOfBoundsException $ex) {
            throw new SerializationException('Failed to deserialize value', 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        try {
            $encodedValue = $this->encoders->getEncoderForValue($value)
                ->encode($value, new EncodingContext());
        } catch (EncodingException | InvalidArgumentException | OutOfBoundsException $ex) {
            throw new SerializationException('Failed to serialize value', 0, $ex);
        }

        if (!($serializedValue = json_encode($encodedValue))) {
            throw new SerializationException('Failed to serialize value: ' . json_last_error_msg());
        }

        return $serializedValue;
    }
}
