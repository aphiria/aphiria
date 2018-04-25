<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

use Closure;
use OutOfBoundsException;

/**
 * Defines a contract mapper registry
 */
class ContractMapperRegistry
{
    /** @var IContractMapper[] The mapping of types to contract mappers */
    private $contractMappersByType = [];

    /**
     * Gets the contract mapper for a type
     *
     * @param string $type The type whose contract mapper we want
     * @return IContractMapper The contract mapper for the input type
     * @throws OutOfBoundsException Thrown if the type does not have a contract mapper
     */
    public function getContractMapperForType(string $type): IContractMapper
    {
        $normalizedType = $this->normalizeType($type);

        if (!isset($this->contractMappersByType[$normalizedType])) {
            // Use the input type name to make the exception message more meaningful
            throw new OutOfBoundsException("No contract mapper registered for type \"$type\"");
        }

        return $this->contractMappersByType[$normalizedType];
    }

    /**
     * Gets the contract mapper for a value
     *
     * @param mixed $value The value whose contract mapper we want
     * @return IContractMapper The contract mapper for the input value
     * @throws OutOfBoundsException Thrown if the value does not have a contract mapper
     */
    public function getContractMapperForValue($value): IContractMapper
    {
        // Note: The type is normalized in getContractMapperForType()
        return $this->getContractMapperForType(TypeResolver::resolveType($value));
    }

    /**
     * Registers a closure contract mapper
     *
     * @param string $type The type that the contract mapper applies to
     * @param Closure $toContractClosure The closure that maps the type to a contract
     * @param Closure $fromContractClosure The closure that maps the contract to the type
     */
    public function registerClosureContractContractMapper(
        string $type,
        Closure $toContractClosure,
        Closure $fromContractClosure
    ): void {
        // Note: The type is normalized in registerContractMapper()
        $this->registerContractMapper(new ClosureContractMapper($type, $toContractClosure, $fromContractClosure));
    }

    /**
     * Registers a contract mapper
     *
     * @param IContractMapper $contractMapper The contract mapper to register
     */
    public function registerContractMapper(IContractMapper $contractMapper): void
    {
        $normalizedType = $this->normalizeType($contractMapper->getType());
        $this->contractMappersByType[$normalizedType] = $contractMapper;
    }

    /**
     * Normalizes a type, eg "integer" to "int", for usage as keys in the registry
     *
     * @param string $type The type to normalize
     * @return string The normalized type
     */
    private function normalizeType(string $type): string
    {
        switch ($type) {
            case 'boolean':
            case 'bool':
                return 'bool';
            case 'float':
            case 'double':
                return 'float';
            case 'int':
            case 'integer':
                return 'int';
            default:
                return $type;
        }
    }
}
