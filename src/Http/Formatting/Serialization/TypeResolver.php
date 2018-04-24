<?php

/*
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2018 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

namespace Opulence\Net\Http\Formatting\Serialization;

/**
 * Defines a type resolver
 */
class TypeResolver
{
    /**
     * Gets the type of the input value
     * This is useful for getting around PHP's type shortcomings
     *
     * @param mixed $value The value whose type we want
     * @return string The type of the input value
     */
    public static function resolveType($value): string
    {
        return \is_object($value) ? \get_class($value) : \gettype($value);
    }
}
