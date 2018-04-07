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
 * Defines the interface for contract mapper buses to implement
 */
interface IContractMapperBus
{
    /**
     * Maps an array contract to a list of instances of the input type
     *
     * @param ArrayContract $contract The contract to map from
     * @param string $type The type to map to
     * @return array A list of instances of the input type
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input type
     */
    public function mapFromArrayContract(ArrayContract $contract, string $type): array;

    /**
     * Maps a boolean contract to an instance of the input type
     *
     * @param BoolContract $contract The contract to map from
     * @param string $type The type to map to
     * @return mixed An instance of the input type
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input type
     */
    public function mapFromBoolContract(BoolContract $contract, string $type);

    /**
     * Maps a dictionary contract to an instance of the input type
     *
     * @param DictionaryContract $contract The contract to map from
     * @param string $type The type to map to
     * @return mixed An instance of the input type
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input type
     */
    public function mapFromDictionaryContract(DictionaryContract $contract, string $type);

    /**
     * Maps a float contract to an instance of the input type
     *
     * @param FloatContract $contract The contract to map from
     * @param string $type The type to map to
     * @return mixed An instance of the input type
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input type
     */
    public function mapFromFloatContract(FloatContract $contract, string $type);

    /**
     * Maps a integer contract to an instance of the input type
     *
     * @param IntContract $contract The contract to map from
     * @param string $type The type to map to
     * @return mixed An instance of the input type
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input type
     */
    public function mapFromIntContract(IntContract $contract, string $type);

    /**
     * Maps a string contract to an instance of the input type
     *
     * @param StringContract $contract The contract to map from
     * @param string $type The type to map to
     * @return mixed An instance of the input type
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input type
     */
    public function mapFromStringContract(StringContract $contract, string $type);

    /**
     * Maps a list of values to an array contract
     *
     * @param array $values The list of values to map
     * @return ArrayContract The contract
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input value's type
     */
    public function mapToArrayContract(array $values): ArrayContract;

    /**
     * Maps a value to a boolean contract
     *
     * @param mixed $value The value to map
     * @return BoolContract The contract
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input value's type
     */
    public function mapToBoolContract($value): BoolContract;

    /**
     * Maps a value to a dictionary contract
     *
     * @param mixed $value The value to map
     * @return DictionaryContract The contract
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input value's type
     */
    public function mapToDictionaryContract($value): DictionaryContract;

    /**
     * Maps a value to a float contract
     *
     * @param mixed $value The value to map
     * @return FloatContract The contract
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input value's type
     */
    public function mapToFloatContract($value): FloatContract;

    /**
     * Maps a value to a integer contract
     *
     * @param mixed $value The value to map
     * @return IntContract The contract
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input value's type
     */
    public function mapToIntContract($value): IntContract;

    /**
     * Maps a value to a string contract
     *
     * @param mixed $value The value to map
     * @return StringContract The contract
     * @throws OutOfBoundsException Thrown if there is no contract mapper registered for the input value's type
     */
    public function mapToStringContract($value): StringContract;
}
