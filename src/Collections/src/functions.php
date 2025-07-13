<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2025 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections\Functions;

use Aphiria\Collections\ArrayList;
use Aphiria\Collections\HashSet;
use Aphiria\Collections\HashTable;
use Aphiria\Collections\ImmutableArrayList;
use Aphiria\Collections\ImmutableHashSet;
use Aphiria\Collections\ImmutableHashTable;
use Aphiria\Collections\KeyValuePair;
use Aphiria\Collections\Queue;
use Aphiria\Collections\Stack;

/**
 * Creates an ArrayList from an array of values
 *
 * @template T
 * @param list<T> $values The values to add to the list
 * @return ArrayList<T> The created list
 */
function array_list(array $values = []): ArrayList
{
    return new ArrayList($values);
}

/**
 * Creates a HashSet from an array of values
 *
 * @template T
 * @param list<T> $values The values to add to the hash set
 * @return HashSet<T> The created hash set
 */
function hash_set(array $values = []): HashSet
{
    return new HashSet($values);
}

/**
 * Creates a HashTable from an array of key-value pairs
 *
 * @template TKey
 * @template TValue
 * @param list<KeyValuePair<TKey, TValue>>|array<TKey, TValue> $kvps The values to add to the hash table
 * @return HashTable<TKey, TValue> The created hash table
 */
function hash_table(array $kvps = []): HashTable
{
    return new HashTable($kvps);
}

/**
 * Creates an ImmutableArrayList from an array of values
 *
 * @template T
 * @param list<T> $values The values to add to the list
 * @return ImmutableArrayList<T> The created list
 */
function immutable_array_list(array $values = []): ImmutableArrayList
{
    return new ImmutableArrayList($values);
}

/**
 * Creates an ImmutableHashSet from an array of values
 *
 * @template T
 * @param list<T> $values The values to add to the hash set
 * @return ImmutableHashSet<T> The created hash set
 */
function immutable_hash_set(array $values = []): ImmutableHashSet
{
    return new ImmutableHashSet($values);
}

/**
 * Creates an ImmutableHashTable from an array of key-value pairs
 *
 * @template TKey
 * @template TValue
 * @param list<KeyValuePair<TKey, TValue>>|array<TKey, TValue> $kvps The key-value pairs to add to the hash table
 * @return ImmutableHashTable<TKey, TValue> The created hash table
 */
function immutable_hash_table(array $kvps = []): ImmutableHashTable
{
    return new ImmutableHashTable($kvps);
}

/**
 * Creates a Queue
 *
 * @template T
 * @return Queue<T> The created queue
 */
function queue(): Queue
{
    return new Queue();
}

/**
 * Creates a Stack
 *
 * @template T
 * @return Stack<T> The created stack
 */
function stack(): Stack
{
    return new Stack();
}
