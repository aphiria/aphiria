<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

/**
 * Defines a JSON serializer
 */
class JsonSerializer implements ISerializer
{
    /** @var ContractRegistry The registry of contracts */
    private $contracts;
    /** @var IEncodingInterceptor[] The list of encoding interceptors to run contracts through */
    private $encodingInterceptors = [];

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
     * @inheritdoc
     */
    public function deserialize(string $value, string $type)
    {
        $decodedValue = json_decode($value, true);

        if (($jsonErrorCode = json_last_error()) !== JSON_ERROR_NONE) {
            throw new SerializationException('Failed to deserialize value: ' . json_last_error_msg());
        }

        if ($decodedValue === null) {
            return null;
        }

        try {
            return $this->contracts->getContractForType($type)
                ->decode($decodedValue, $this->encodingInterceptors);
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
            $encodedValue = $value === null ? null : $this->contracts->getContractForValue($value)
                ->encode($value, $this->encodingInterceptors);
        } catch (EncodingException $ex) {
            throw new SerializationException('Failed to serialize value', 0, $ex);
        }

        if (!($jsonEncodedContract = json_encode($encodedValue))) {
            throw new SerializationException('Failed to serialize value: ' . json_last_error_msg());
        }

        return $jsonEncodedContract;
    }
}
