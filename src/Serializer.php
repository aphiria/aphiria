<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization;

use Opulence\Serialization\Encoding\EncoderRegistry;
use Opulence\Serialization\Encoding\EncodingException;
use Opulence\Serialization\Encoding\IEncodingInterceptor;
use OutOfBoundsException;

/**
 * Defines a base class for serializers
 */
abstract class Serializer implements ISerializer
{
    /** @var EncoderRegistry The registry of encoders */
    protected $encoders;
    /** @var IEncodingInterceptor[] The list of encoding interceptors to run encoders through */
    protected $encodingInterceptors = [];

    /**
     * @param EncoderRegistry $encoders The registry of encoder
     * @param IEncodingInterceptor[] $encodingInterceptors The list of encoding interceptors to run encoders through
     */
    public function __construct(EncoderRegistry $encoders, array $encodingInterceptors = [])
    {
        $this->encoders = $encoders;
        $this->encodingInterceptors = $encodingInterceptors;
    }

    /**
     * Decodes a value
     *
     * @param mixed $value The value to decode
     * @param string $type The type of value to decode to
     * @param bool $isArrayOfType Whether or not to treat the value as an array of values
     * @return mixed The decoded value
     * @throws OutOfBoundsException Thrown if no encoder exists for the input value
     * @throws EncodingException Thrown if there was an error decoding the value
     */
    protected function decodeValue($value, string $type, bool $isArrayOfType)
    {
        // Don't bother going to the encoders if it's an "empty" value
        if ($value === null || $value === []) {
            return $value;
        }

        $encoder = $this->encoders->getEncoderForType($type);

        if (!$isArrayOfType) {
            return $encoder->decode($value, $this->encodingInterceptors);
        }

        $decodedValues = [];

        foreach ($value as $singleValue) {
            $decodedValues[] = $encoder->decode($singleValue, $this->encodingInterceptors);
        }

        return $decodedValues;
    }

    /**
     * Encodes a value
     *
     * @param mixed $value The value to encode
     * @return mixed The encoded value
     * @throws OutOfBoundsException Thrown if no encoder exists for the input value
     * @throws EncodingException Thrown if there was an error encoding the value
     */
    protected function encodeValue($value)
    {
        if ($value === null) {
            return null;
        }

        if (!\is_array($value)) {
            return $this->encoders->getEncoderForValue($value)
                ->encode($value, $this->encodingInterceptors);
        }

        $encodedValue = [];

        if (\count($value) > 0) {
            // Here we assume the list contains homogenous types
            $encoder = $this->encoders->getEncoderForValue($value[0]);

            foreach ($value as $singleValue) {
                $encodedValue[] = $encoder->encode($singleValue, $this->encodingInterceptors);
            }
        }

        return $encodedValue;
    }
}
