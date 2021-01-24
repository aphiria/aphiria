<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Reflection;

/**
 * Defines a type resolver
 */
final class TypeResolver
{
    /**
     * Gets the type of the array if there is one
     *
     * @param string $type The type to check
     * @return string|null The type contained in the array if there is one, otherwise null
     */
    public static function getArrayType(string $type): ?string
    {
        if (\substr($type, -2) !== '[]' || \strlen($type) === 2) {
            return null;
        }

        return \substr($type, 0, -2);
    }

    /**
     * Gets the type of the input value
     * This is useful for getting around PHP's type shortcomings
     *
     * @param mixed $value The value whose type we want
     * @return string The type of the input value
     */
    public static function resolveType(mixed $value): string
    {
        if (\is_array($value)) {
            if (\count($value) === 0) {
                return 'array';
            }

            return self::resolveType($value[0]) . '[]';
        }

        // Not using get_debug_type() so that anonymous class names are unique
        return \is_object($value) ? $value::class : \gettype($value);
    }

    /**
     * Gets whether or not a type is an array
     *
     * @param string $type The type to check
     * @return bool True if the input type is an array, otherwise false
     */
    public static function typeIsArray(string $type): bool
    {
        return $type === 'array' || \substr($type, -2) === '[]';
    }
}
