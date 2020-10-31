<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/0.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections;

use Countable;
use IteratorAggregate;
use RuntimeException;

/**
 * Defines the interface for sets to implement
 */
interface ISet extends Countable, IteratorAggregate
{
    /**
     * Adds a value
     *
     * @param mixed $value The value to add
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function add(mixed $value): void;

    /**
     * Adds a range of values
     *
     * @param array $values The values to add
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
     * @param mixed $value The value to search for
     * @return bool True if the value exists, otherwise false
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function containsValue(mixed $value): bool;

    /**
     * Intersects the values of the input array with the values already in the set
     *
     * @param array $values The values to intersect with
     * @throws RuntimeException Thrown if the values' keys could not be calculated
     */
    public function intersect(array $values): void;

    /**
     * Removes a value from the set
     *
     * @param mixed $value The value to remove
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function removeValue(mixed $value): void;

    /**
     * Sorts the values of the set
     *
     * @param callable(mixed, mixed): int $comparer The comparer to sort with
     */
    public function sort(callable $comparer): void;

    /**
     * Gets all of the values as an array
     *
     * @return array All of the values
     */
    public function toArray(): array;

    /**
     * Unions the values of the input array with the values already in the set
     *
     * @param array $values The values to union with
     * @throws RuntimeException Thrown if the values' keys could not be calculated
     */
    public function union(array $values): void;
}
