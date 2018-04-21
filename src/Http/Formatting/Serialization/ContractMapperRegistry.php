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
        if (!isset($this->contractMappersByType[$type])) {
            throw new OutOfBoundsException("No contract mapper registered for type \"$type\"");
        }

        return $this->contractMappersByType[$type];
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
        $type = \is_object($value) ? \get_class($value) : gettype($value);

        return $this->getContractMapperForType($type);
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
        $this->registerContractMapper(new ClosureContractMapper($type, $toContractClosure, $fromContractClosure));
    }

    /**
     * Registers a contract mapper
     *
     * @param IContractMapper $contractMapper The contract mapper to register
     */
    public function registerContractMapper(IContractMapper $contractMapper): void
    {
        $this->contractMappersByType[$contractMapper->getType()] = $contractMapper;
    }
}
