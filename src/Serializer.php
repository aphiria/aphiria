<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Serialization;

use Opulence\Serialization\Encoding\ContractRegistry;
use Opulence\Serialization\Encoding\EncodingException;
use Opulence\Serialization\Encoding\IEncodingInterceptor;
use OutOfBoundsException;

/**
 * Defines a base class for serializers
 */
abstract class Serializer implements ISerializer
{
    /** @var ContractRegistry The registry of contracts */
    protected $contracts;
    /** @var IEncodingInterceptor[] The list of encoding interceptors to run contracts through */
    protected $encodingInterceptors = [];

    /**
     * @param ContractRegistry $contracts The registry of contract
     * @param IEncodingInterceptor[] $encodingInterceptors The list of encoding interceptors to run contracts through
     */
    public function __construct(ContractRegistry $contracts, array $encodingInterceptors = [])
    {
        $this->contracts = $contracts;
        $this->encodingInterceptors = $encodingInterceptors;
    }

    /**
     * Decodes a value
     *
     * @param mixed $value The value to decode
     * @param string $type The type of value to decode to
     * @param bool $isArrayOfType Whether or not to treat the value as an array of values
     * @return mixed The decoded value
     * @throws OutOfBoundsException Thrown if no contract exists for the input value
     * @throws EncodingException Thrown if there was an error decoding the value
     */
    protected function decodeValue($value, string $type, bool $isArrayOfType)
    {
        // Don't bother going to the contracts if it's an "empty" value
        if ($value === null || $value === []) {
            return $value;
        }

        $contract = $this->contracts->getContractForType($type);

        if (!$isArrayOfType) {
            return $contract->decode($value, $this->encodingInterceptors);
        }

        $decodedValues = [];

        foreach ($value as $singleValue) {
            $decodedValues[] = $contract->decode($singleValue, $this->encodingInterceptors);
        }

        return $decodedValues;
    }

    /**
     * Encodes a value
     *
     * @param mixed $value The value to encode
     * @return mixed The encoded value
     * @throws OutOfBoundsException Thrown if no contract exists for the input value
     * @throws EncodingException Thrown if there was an error encoding the value
     */
    protected function encodeValue($value)
    {
        if ($value === null) {
            return null;
        }

        if (!\is_array($value)) {
            return $this->contracts->getContractForValue($value)
                ->encode($value, $this->encodingInterceptors);
        }

        $encodedValue = [];

        if (\count($value) > 0) {
            // Here we assume the list contains homogenous types
            $contract = $this->contracts->getContractForValue($value[0]);

            foreach ($value as $singleValue) {
                $encodedValue[] = $contract->encode($singleValue, $this->encodingInterceptors);
            }
        }

        return $encodedValue;
    }
}
