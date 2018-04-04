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
 * Defines the integer contract
 */
class IntContract
{
    /** @var int The integer value */
    private $value;

    /**
     * @param int $value The integer value
     */
    public function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * Gets the integer value
     *
     * @return int The integer value
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
