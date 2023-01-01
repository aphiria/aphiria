<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2023 David Young
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
 * Defines the interface for lists to implement
 *
 * @template T
 * @extends ArrayAccess<array-key, T>
 * @extends IteratorAggregate<array-key, T>
 */
interface IList extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Adds a value
     *
     * @param T $value The value to add
     */
    public function add(mixed $value): void;

    /**
     * Adds a range of values
     *
     * @param list<T> $values The values to add
     */
    public function addRange(array $values): void;

    /**
     * Clears all values from the list
     */
    public function clear(): void;

    /**
     * Gets whether or not the value exists
     *
     * @param T $value The value to search for
     * @return bool True if the value exists, otherwise false
     */
    public function containsValue(mixed $value): bool;

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
     * Inserts the value at an index
     *
     * @param int $index The index to insert at
     * @param T $value The value to insert
     */
    public function insert(int $index, mixed $value): void;

    /**
     * Intersects the values of the input array with the values already in the list
     *
     * @param list<T> $values The values to intersect with
     * @return static The intersected list
     */
    public function intersect(array $values): static;

    /**
     * Removes the value at an index
     *
     * @param int $index The index to remove
     */
    public function removeIndex(int $index): void;

    /**
     * Removes the value from the list
     *
     * @param T $value The value to remove
     */
    public function removeValue(mixed $value): void;

    /**
     * Reverses the list
     *
     * @return static The reversed list
     */
    public function reverse(): static;

    /**
     * Sorts the values of the list
     *
     * @param Closure(T, T): int $comparer The comparer to sort with
     * @return static The sorted list
     */
    public function sort(Closure $comparer): static;

    /**
     * Gets all of the values as an array
     *
     * @return list<T> All of the values
     */
    public function toArray(): array;

    /**
     * Unions the values of the input array with the values already in the list
     *
     * @param list<T> $values The values to union with
     * @return static The unioned list
     */
    public function union(array $values): static;
}
