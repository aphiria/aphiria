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
use OutOfBoundsException;
use RuntimeException;

/**
 * Defines the interface for dictionaries to implement
 *
 * @template TKey
 * @template TValue
 * @extends ArrayAccess<TKey, TValue>
 * @extends IteratorAggregate<TKey, TValue>
 */
interface IDictionary extends ArrayAccess, Countable, IteratorAggregate
{
    /** @var list<TKey> The list of keys in the dictionary */
    public array $keys { get; }
    /** @var list<TValue> The list of values in the dictionary */
    public array $values { get; }

    /**
     * Adds a value
     *
     * @param TKey $key The key to add
     * @param TValue $value The value to add
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function add(mixed $key, mixed $value): void;

    /**
     * Adds multiple values
     *
     * @param list<KeyValuePair<TKey, TValue>> $values The values to add
     * @throws RuntimeException Thrown if the values' keys could not be calculated
     */
    public function addRange(array $values): void;

    /**
     * Clears all values from the dictionary
     */
    public function clear(): void;

    /**
     * Gets whether or not the key exists
     *
     * @param TKey $key The key to check for
     * @return bool True if the key exists, otherwise false
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function containsKey(mixed $key): bool;

    /**
     * Gets whether or not the value exists in the dictionary
     *
     * @param TValue $value The value to search for
     * @return bool True if the value exists, otherwise false
     */
    public function containsValue(mixed $value): bool;

    /**
     * Gets the value of the key
     *
     * @param TKey $key The key to get
     * @return TValue The value at the key
     * @throws OutOfBoundsException Thrown if the key could not be found
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function get(mixed $key): mixed;

    /**
     * Removes a key
     *
     * @param TKey $key The key to remove
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function removeKey(mixed $key): void;

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
