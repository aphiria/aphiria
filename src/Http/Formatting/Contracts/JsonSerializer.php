<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Contracts;

/**
 * Defines a JSON serializer
 */
class JsonSerializer implements ISerializer
{
    /** @var ContractMapperRegistry The registry of contract mappers */
    private $contractMappers;

    /**
     * @param ContractMapperRegistry $contractMappers The registry of contract mappers
     */
    public function __construct(ContractMapperRegistry $contractMappers)
    {
        $this->contractMappers = $contractMappers;
    }

    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!! Todo: support property name resolution
    /**
     * @inheritdoc
     */
    public function deserialize(string $value, string $type)
    {
        // Todo: Where/how do I do fuzzy matching to get the "proper" property names in the case that we're deserializing a dictionary?
        // Todo: Does that occur outside of here?  That would require some sort of type info, right?
        $phpValue = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SerializationException('Invalid JSON');
        }

        return $this->contractMappers->getContractMapperForType($type)
            ->mapFromContract($phpValue, $type);
    }

    /**
     * @inheritdoc
     */
    public function serialize($value): string
    {
        // Todo: Send the contract through the property name resolver
        $serializableValue = $this->contractMappers->getContractMapperForValue($value)
            ->mapToContract($value);

        return json_encode($serializableValue);
    }
}
