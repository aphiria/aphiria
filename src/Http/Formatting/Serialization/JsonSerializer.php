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
    /** @var ContractMapperRegistry The registry of contract mappers */
    private $contractMappers;
    /** @var ISerializationInterceptor[] The list of serialization interceptors to run contracts through */
    private $serializationInterceptors = [];

    /**
     * @param ContractMapperRegistry $contractMappers The registry of contract mappers
     * @param ISerializationInterceptor[] $serializationInterceptors The list of serialization interceptors
     */
    public function __construct(ContractMapperRegistry $contractMappers, array $serializationInterceptors = [])
    {
        $this->contractMappers = $contractMappers;
        $this->serializationInterceptors = $serializationInterceptors;
    }

    /**
     * @inheritdoc
     */
    public function deserialize(string $value, string $type)
    {
        $contract = json_decode($value, true);

        if (($jsonErrorCode = json_last_error()) !== JSON_ERROR_NONE) {
            throw $this->createDeserializationException($jsonErrorCode);
        }

        foreach ($this->serializationInterceptors as $serializationInterceptor) {
            $contract = $serializationInterceptor->onDeserialization($contract, $type);
        }

        return $this->contractMappers->getContractMapperForType($type)
            ->mapFromContract($contract, $type);
    }

    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        $contract = $this->contractMappers->getContractMapperForValue($value)
            ->mapToContract($value);

        foreach ($this->serializationInterceptors as $serializationInterceptor) {
            $contract = $serializationInterceptor->onSerialization($contract);
        }

        if (!($jsonEncodedContract = json_encode($contract))) {
            throw new SerializationException('Failed to serialize contract');
        }

        return $jsonEncodedContract;
    }

    /**
     * Creates a deserialization exception from a JSON error code
     *
     * @param int $jsonErrorCode The JSON error code
     * @return SerializationException The exception to throw
     */
    private function createDeserializationException(int $jsonErrorCode): SerializationException
    {
        switch ($jsonErrorCode) {
            case JSON_ERROR_DEPTH:
                $message = 'The maximum stack depth has been exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $message = 'Invalid or malformed JSON';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $message = 'JSON control character error, possibly incorrectly encoded';
                break;
            case JSON_ERROR_SYNTAX:
                $message = 'JSON syntax error';
                break;
            case JSON_ERROR_UTF8:
                $message = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            case JSON_ERROR_RECURSION:
                $message = 'One or more recursive references in the value encoded';
                break;
            case JSON_ERROR_INF_OR_NAN:
                $message = 'One or more NAN or INF values in the value to be encoded';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $message = 'A value of a type that cannot be encoded was given';
                break;
            case JSON_ERROR_INVALID_PROPERTY_NAME:
                $message = 'A property name that cannot be encoded was given';
                break;
            case JSON_ERROR_UTF16:
                $message = 'Malformed UTF-16 characters, possibly incorrectly encoded';
                break;
            default:
                $message = 'Unknown JSON error';
        }

        return new SerializationException("Failed to deserialize value: $message");
    }
}
