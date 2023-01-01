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

use Closure;
use Countable;
use IteratorAggregate;
use RuntimeException;

/**
 * Defines the interface for sets to implement
 *
 * @template T
 * @extends IteratorAggregate<array-key, T>
 */
interface ISet extends Countable, IteratorAggregate
{
    /**
     * Adds a value
     *
     * @param T $value The value to add
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function add(mixed $value): void;

    /**
     * Adds a range of values
     *
     * @param list<T> $values The values to add
     * @throws RuntimeException Thrown if the values' keys could not be calculated
     */
    public function addRange(array $values): void;

    /**
     * Clears all values from the set
     */
    public function clear(): void;

    /**
     * Gets whether or not the value exists
     *
     * @param T $value The value to search for
     * @return bool True if the value exists, otherwise false
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function containsValue(mixed $value): bool;

    /**
     * Intersects the values of the input array with the values already in the set
     *
     * @param list<T> $values The values to intersect with
     * @return static The intersected set
     * @throws RuntimeException Thrown if the values' keys could not be calculated
     */
    public function intersect(array $values): static;

    /**
     * Removes a value from the set
     *
     * @param T $value The value to remove
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function removeValue(mixed $value): void;

    /**
     * Sorts the values of the set
     *
     * @param Closure(T, T): int $comparer The comparer to sort with
     * @return static The sorted set
     */
    public function sort(Closure $comparer): static;

    /**
     * Gets all of the values as an array
     *
     * @return list<T> All of the values
     */
    public function toArray(): array;

    /**
     * Unions the values of the input array with the values already in the set
     *
     * @param list<T> $values The values to union with
     * @return static The unioned set
     * @throws RuntimeException Thrown if the values' keys could not be calculated
     */
    public function union(array $values): static;
}
