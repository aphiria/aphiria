<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2021 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use OutOfBoundsException;
use RuntimeException;

/**
 * Defines the interface for immutable dictionaries to implement
 *
 * @template TKey
 * @template TValue
 */
interface IImmutableDictionary extends ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Gets whether or not the key exists
     *
     * @param TKey $key The key to check for
     * @return bool True if the key exists, otherwise false
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function containsKey(mixed $key): bool;

    /**
     * Gets whether or not the value exists in the hash table
     *
     * @param TValue $value The value to search for
     * @return bool True if the value exists, otherwise false
     */
    public function containsValue(mixed $value): bool;

    /**
     * Gets the value of the key
     *
     * @param TKey $key The key to get
     * @return TValue The value if it was found
     * @throws OutOfBoundsException Thrown if the key could not be found
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function get(mixed $key): mixed;

    /**
     * Gets the list of keys in the dictionary
     *
     * @return list<TKey> The list of keys in the dictionary
     */
    public function getKeys(): array;

    /**
     * Gets the list of values in the dictionary
     *
     * @return list<TValue> The list of values in the dictionary
     */
    public function getValues(): array;

    /**
     * Gets all of the values as an array of key-value pairs
     *
     * @return list<KeyValuePair<TKey, TValue>> All of the values as a list of key-value pairs
     */
    public function toArray(): array;

    /**
     * Attempts to get the value at a key
     *
     * @param TKey $key The key to get
     * @param TValue|null $value The value of the key, if it exists
     * @param-out TValue|null $value
     * @return bool True if the key existed, otherwise false
     */
    public function tryGet(mixed $key, mixed &$value): bool;
}
