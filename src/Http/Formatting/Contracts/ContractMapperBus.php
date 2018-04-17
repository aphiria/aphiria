<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Contracts;

use OutOfBoundsException;

/**
 * Defines the contract mapper bus
 */
class ContractMapperBus implements IContractMapperBus
{
    /** @var ContractMapperRegistry The registry of contract mappers */
    private $contractMapperRegistry;

    /**
     * @param ContractMapperRegistry $contractMapperRegistry The registry of contract mappers
     */
    public function __construct(ContractMapperRegistry $contractMapperRegistry)
    {
        $this->contractMapperRegistry = $contractMapperRegistry;
    }

    /**
     * @inheritdoc
     */
    public function mapFromArrayContract(ArrayContract $contract, string $type): array
    {
        return $this->mapFromContract($contract, $type);
    }

    /**
     * @inheritdoc
     */
    public function mapFromBoolContract(BoolContract $contract, string $type)
    {
        return $this->mapFromContract($contract, $type);
    }

    /**
     * @inheritdoc
     */
    public function mapFromDictionaryContract(DictionaryContract $contract, string $type)
    {
        return $this->mapFromContract($contract, $type);
    }

    /**
     * @inheritdoc
     */
    public function mapFromFloatContract(FloatContract $contract, string $type)
    {
        return $this->mapFromContract($contract, $type);
    }

    /**
     * @inheritdoc
     */
    public function mapFromIntContract(IntContract $contract, string $type)
    {
        return $this->mapFromContract($contract, $type);
    }

    /**
     * @inheritdoc
     */
    public function mapFromStringContract(StringContract $contract, string $type)
    {
        return $this->mapFromContract($contract, $type);
    }

    /**
     * @inheritdoc
     */
    public function mapToArrayContract(array $values): ArrayContract
    {
        return $this->mapToContract($values);
    }

    /**
     * @inheritdoc
     */
    public function mapToBoolContract($value): BoolContract
    {
        return $this->mapToContract($value);
    }

    /**
     * @inheritdoc
     */
    public function mapToDictionaryContract($value): DictionaryContract
    {
        return $this->mapToContract($value);
    }

    /**
     * @inheritdoc
     */
    public function mapToFloatContract($value): FloatContract
    {
        return $this->mapToContract($value);
    }

    /**
     * @inheritdoc
     */
    public function mapToIntContract($value): IntContract
    {
        return $this->mapToContract($value);
    }

    /**
     * @inheritdoc
     */
    public function mapToStringContract($value): StringContract
    {
        return $this->mapToContract($value);
    }

    /**
     * Maps a contract to an instance of a type
     *
     * @param IContract $contract The contract to map from
     * @param string $type The type to convert to
     * @return mixed An instance of the input type
     * @throws OutOfBoundsException Thrown if there is no contract mapper for the input type
     */
    private function mapFromContract(IContract $contract, string $type)
    {
        return $this->contractMapperRegistry->getContractMapperForType($type)
            ->mapFromContract($contract);
    }

    /**
     * Maps data to a contract
     *
     * @param mixed $value The value to map
     * @return IContract The contract
     * @throws OutOfBoundsException Thrown if there is no contract mapper for the input value
     */
    private function mapToContract($value): IContract
    {
        $type = \is_object($value) ? \get_class($value) : \gettype($value);

        return $this->contractMapperRegistry->getContractMapperForType($type)
            ->mapToContract($value);
    }
}
