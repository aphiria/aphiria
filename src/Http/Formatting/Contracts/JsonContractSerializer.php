<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Contracts;

use InvalidArgumentException;
use TypeError;

/**
 * Defines a JSON contract serializer
 */
class JsonContractSerializer implements IContractSerializer
{
    // !!!!!!!!!!!!!!!!!!!!!!!!!!!!! Todo: support propert name resolution
    /**
     * @inheritdoc
     */
    public function deserializeContract(string $serializedContract, string $contractType): IContract
    {
        if (!is_subclass_of($contractType, IContract::class)) {
            throw new InvalidArgumentException("\"$contractType\" does not implement " . IContract::class);
        }

        // Todo: Where/how do I do fuzzy matching to get the "proper" property names in the case that we're deserializing a dictionary?
        // Todo: Does that occur outside of here?  That would require some sort of type info, right?
        $phpValue = json_decode($serializedContract, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SerializationException('Invalid JSON');
        }

        try {
            return new $contractType($phpValue);
        } catch (TypeError $ex) {
            throw new SerializationException("Failed to create contract of type \"$contractType\"", 0, $ex);
        }
    }

    /**
     * @inheritdoc
     */
    public function serializeContract(IContract $contract): string
    {
        // Todo: Send the contract through the property name resolver
        return json_encode($contract->getValue());
    }
}
