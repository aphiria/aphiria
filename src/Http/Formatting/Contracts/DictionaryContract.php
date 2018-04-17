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
 * Defines a contract that is a dictionary of data
 */
class DictionaryContract implements IContract
{
    /** @var array The dictionary values in the contract */
    private $values;

    /**
     * @param array $values The dictionary values in the contract
     */
    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * @inheritdoc
     */
    public function getValue(): array
    {
        return $this->values;
    }
}
