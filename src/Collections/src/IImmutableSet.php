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

use Closure;
use Countable;
use IteratorAggregate;
use RuntimeException;

/**
 * Defines the interface for immutable sets to implement
 *
 * @template T
 * @extends IteratorAggregate<array-key, T>
 */
interface IImmutableSet extends Countable, IteratorAggregate
{
    /**
     * Gets whether or not the value exists
     *
     * @param T $value The value to search for
     * @return bool True if the value exists, otherwise false
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function containsValue(mixed $value): bool;

    /**
     * Applies a filter to the values in the set
     *
     * @param Closure(T): bool $callback The filter callback that takes in a value and returns whether to include it in the filtered set
     * @return static A filtered instance of the set
     */
    public function filter(Closure $callback): static;

    /**
     * Applies a mapping to each value in the set
     *
     * @param Closure(T): T $callback The map callback
     * @return static A set with the map applied to each value
     */
    public function map(Closure $callback): static;

    /**
     * Gets all of the values as an array
     *
     * @return list<T> All of the values
     */
    public function toArray(): array;
}
