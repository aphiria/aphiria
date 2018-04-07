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
        return $this->contractMapperRegistry->getContractMapperForType($type)
            ->mapFromContract($contract);
    }

    /**
     * @inheritdoc
     */
    public function mapFromBoolContract(BoolContract $contract, string $type)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function mapFromDictionaryContract(DictionaryContract $contract, string $type)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function mapFromFloatContract(FloatContract $contract, string $type)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function mapFromIntContract(IntContract $contract, string $type)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function mapFromStringContract(StringContract $contract, string $type)
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function mapToArrayContract(array $values): ArrayContract
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function mapToBoolContract($value): BoolContract
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function mapToDictionaryContract($value): DictionaryContract
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function mapToFloatContract($value): FloatContract
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function mapToIntContract($value): IntContract
    {
        throw new \Exception('Not implemented');
    }

    /**
     * @inheritdoc
     */
    public function mapToStringContract($value): StringContract
    {
        throw new \Exception('Not implemented');
    }
}
