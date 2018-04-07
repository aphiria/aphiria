<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Contracts;

use Closure;

/**
 * Defines a contract mapper that uses closures to map to and from a contract
 */
class ClosureContractMapper implements IContractMapper
{
    /** @var string The underlying type that we're mapping to a contract */
    private $type;
    /** @var Closure The closure that maps an instance to a contract */
    private $toContractClosure;
    /** @var Closure The closure that maps from a contract to an instance */
    private $fromContractClosure;

    /**
     * @param string $type The underlying type that we're mapping to a contract
     * @param Closure $toContractClosure The closure that maps an instance to a contract
     * @param Closure $fromContractClosure The closure that maps from a contract to an instance
     */
    public function __construct(string $type, Closure $toContractClosure, Closure $fromContractClosure)
    {
        $this->type = $type;
        $this->toContractClosure = $toContractClosure;
        $this->fromContractClosure = $fromContractClosure;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function mapFromContract($contract)
    {
        return ($this->fromContractClosure)($contract);
    }

    /**
     * @inheritdoc
     */
    public function mapToContract($data)
    {
        return ($this->toContractClosure)($data);
    }
}
