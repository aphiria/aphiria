<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2024 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections;

use ArrayAccess;
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
     * Gets all of the values as an array
     *
     * @return list<T> All of the values
     */
    public function toArray(): array;
}
