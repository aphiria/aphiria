<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (c) 2019 David Young
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
 * Defines the base class for serializers to extend
 */
abstract class Serializer implements ISerializer
{
    /** @var EncoderRegistry The encoder registry to use to encode/decode values */
    protected $encoders;

    /**
     * @param EncoderRegistry|null $encoders The encoder registry to use to encode/decode values
     */
    public function __construct(EncoderRegistry $encoders = null)
    {
        $this->encoders = $encoders ?? (new DefaultEncoderRegistrant)->registerDefaultEncoders(new EncoderRegistry);
    }

    /**
     * Decodes a value to a particular type
     *
     * @param mixed $value The value to decode
     * @param string The type to decode to
     * @return mixed An instance of the input type
     * @throws SerializationException Thrown if there was an error decoding the value
     */
    protected function decode($value, string $type)
    {
        try {
            return $this->encoders->getEncoderForType($type)
                ->decode($value, $type, new EncodingContext());
        } catch (EncodingException | InvalidArgumentException | OutOfBoundsException $ex) {
            throw new SerializationException('Failed to deserialize value', 0, $ex);
        }
    }

    /**
     * Encodes a value
     *
     * @param mixed $value The value to encode
     * @return mixed The encoded value
     * @throws SerializationException Thrown if there was an error encoding the value
     */
    protected function encode($value)
    {
        try {
            return $this->encoders->getEncoderForValue($value)
                ->encode($value, new EncodingContext());
        } catch (EncodingException | InvalidArgumentException | OutOfBoundsException $ex) {
            throw new SerializationException('Failed to serialize value', 0, $ex);
        }
    }
}
