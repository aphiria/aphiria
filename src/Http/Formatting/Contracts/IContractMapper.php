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
 * Defines the interface for all contract mappers to implement
 */
interface IContractMapper
{
    /**
     * Gets the type the contract mapper maps
     *
     * @return string The type the contract mapper maps
     */
    public function getType(): string;

    /**
     * Maps a contract to the underlying type
     *
     * @param mixed $contract The contract to map
     * @return mixed An instance of the underlying type
     */
    public function mapFromContract($contract);

    /**
     * Maps an instance of the underlying type to a contract
     *
     * @param mixed $data The instance to map
     * @return mixed The contract
     */
    public function mapToContract($data);
}
