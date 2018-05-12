<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

use DateTime;

/**
 * Defines the default contracts' registrant
 */
class DefaultContractRegistrant
{
    /** @var string The format to use for DateTimes */
    private $dateTimeFormat;

    /**
     * @param string $dateTimeFormat The format to use for DateTimes
     */
    public function __construct(string $dateTimeFormat = DateTime::ISO8601)
    {
        $this->dateTimeFormat = $dateTimeFormat;
    }

    /**
     * Registers default contracts
     *
     * @param ContractRegistry $contracts The contracts to register to
     */
    public function registerContracts(ContractRegistry $contracts): void
    {
        $this->registerBoolContract($contracts);
        $this->registerDateTimeContract($contracts);
        $this->registerFloatContract($contracts);
        $this->registerIntContract($contracts);
        $this->registerStringContract($contracts);
    }

    /**
     * Register an boolean contract
     *
     * @param ContractRegistry $contracts The contract registry to register to
     */
    protected function registerBoolContract(ContractRegistry $contracts): void
    {
        $contracts->registerValueObjectContract(
            'bool',
            function ($value) {
                return (bool)$value;
            },
            function (bool $value) {
                return $value;
            }
        );
    }

    /**
     * Register a DateTime contract
     *
     * @param ContractRegistry $contracts The contract registry to register to
     */
    protected function registerDateTimeContract(ContractRegistry $contracts): void
    {
        $contracts->registerValueObjectContract(
            DateTime::class,
            function ($value) {
                return DateTime::createFromFormat($this->dateTimeFormat, $value);
            },
            function (DateTime $value) {
                return $value->format($this->dateTimeFormat);
            }
        );
    }

    /**
     * Register an float contract
     *
     * @param ContractRegistry $contracts The contract registry to register to
     */
    protected function registerFloatContract(ContractRegistry $contracts): void
    {
        $contracts->registerValueObjectContract(
            'float',
            function ($value) {
                return (float)$value;
            },
            function (float $value) {
                return $value;
            }
        );
    }

    /**
     * Register an integer contract
     *
     * @param ContractRegistry $contracts The contract registry to register to
     */
    protected function registerIntContract(ContractRegistry $contracts): void
    {
        $contracts->registerValueObjectContract(
            'int',
            function ($value) {
                return (int)$value;
            },
            function (int $value) {
                return $value;
            }
        );
    }

    /**
     * Register a string contract
     *
     * @param ContractRegistry $contracts The contract registry to register to
     */
    protected function registerStringContract(ContractRegistry $contracts): void
    {
        $contracts->registerValueObjectContract(
            'string',
            function ($value) {
                return (string)$value;
            },
            function (string $value) {
                return $value;
            }
        );
    }
}
