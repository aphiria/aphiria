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
 * Defines a boolean contract
 */
class BoolContract
{
    /** @var bool The boolean value */
    private $value;

    /**
     * @param bool $value The boolean value
     */
    public function __construct(bool $value)
    {
        $this->value = $value;
    }

    /**
     * Gets the boolean value
     *
     * @return bool The boolean value
     */
    public function getValue(): bool
    {
        return $this->value;
    }
}
