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
 * Defines the string contract
 */
class StringContract
{
    /** @var string The string value */
    private $value;

    /**
     * @param string $value The string value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Gets the string value
     *
     * @return string The string value
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
