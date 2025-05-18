<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections;

use ArrayAccess;
use Closure;
use Countable;
use IteratorAggregate;
use OutOfRangeException;

/**
 * Defines the interface for immutable lists to implement
 *
 * @template T
 * @extends ArrayAccess<array-key, T>
 * @extends IteratorAggregate<array-key, T>
 */
interface IImmutableList extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Gets whether or not the value exists
     *
     * @param T $value The value to search for
     * @return bool True if the value exists, otherwise false
     */
    public function containsValue(mixed $value): bool;

    /**
     * Applies a filter to the values in the list
     *
     * @param Closure(T): bool $callback The filter callback that takes in a value and returns whether to include it in the filtered list
     * @return static A filtered instance of the list
     */
    public function filter(Closure $callback): static;

    /**
     * Gets the value at an index
     *
     * @param int $index The index to get
     * @return T The value if it was found
     * @throws OutOfRangeException Thrown if the index is < 0 or >= than the length of the list
     */
    public function get(int $index): mixed;

    /**
     * Gets the index of a value
     *
     * @param T $value The value to search for
     * @return int|null The index of the value if it was found, otherwise null
     */
    public function indexOf(mixed $value): ?int;

    /**
     * Applies a mapping to each key-value pair in the list
     *
     * @param Closure(T): T $callback The map callback
     * @return static A list with the map applied to each value
     */
    public function map(Closure $callback): static;

    /**
     * Gets all of the values as an array
     *
     * @return list<T> All of the values
     */
    public function toArray(): array;
}
