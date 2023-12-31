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

use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;
use Traversable;

/**
 * Defines a hash table
 *
 * @template TKey
 * @template TValue
 * @implements IDictionary<TKey, TValue>
 */
class HashTable implements IDictionary
{
    /** @var array<string, KeyValuePair<TKey, TValue>> The mapping of hash keys to key-value pairs */
    protected array $hashKeysToKvps = [];
    /** @var KeyHasher The key hasher to use */
    private readonly KeyHasher $keyHasher;

    /**
     * @param list<KeyValuePair<TKey, TValue>> $kvps The list of key-value pairs to add
     * @throws InvalidArgumentException Thrown if the array contains a non-key-value pair
     */
    final public function __construct(array $kvps = [])
    {
        $this->keyHasher = new KeyHasher();
        $this->addRange($kvps);
    }

    /**
     * @inheritdoc
     */
    public function add(mixed $key, mixed $value): void
    {
        $this->hashKeysToKvps[$this->getHashKey($key)] = new KeyValuePair($key, $value);
    }

    /**
     * @inheritdoc
     */
    public function addRange(array $values): void
    {
        foreach ($values as $kvp) {
            /** @psalm-suppress DocblockTypeContradiction We do not want to rely solely on Psalm's type checks */
            if (!$kvp instanceof KeyValuePair) {
                throw new InvalidArgumentException('Value must be instance of ' . KeyValuePair::class);
            }

            $this->hashKeysToKvps[$this->getHashKey($kvp->key)] = $kvp;
        }
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        $this->hashKeysToKvps = [];
    }

    /**
     * @inheritdoc
     */
    public function containsKey(mixed $key): bool
    {
        return \array_key_exists($this->getHashKey($key), $this->hashKeysToKvps);
    }

    /**
     * @inheritdoc
     */
    public function containsValue(mixed $value): bool
    {
        foreach ($this->hashKeysToKvps as $kvp) {
            if ($kvp->value == $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return \count($this->hashKeysToKvps);
    }

    /**
     * @inheritdoc
     * @return TValue
     */
    public function get(mixed $key): mixed
    {
        $hashKey = $this->getHashKey($key);

        if (!$this->containsKey($key)) {
            throw new OutOfBoundsException("Hash key \"$hashKey\" not found");
        }

        return $this->hashKeysToKvps[$hashKey]->value;
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): Traversable
    {
        return new KeyValuePairIterator(\array_values($this->hashKeysToKvps));
    }

    /**
     * @inheritdoc
     */
    public function getKeys(): array
    {
        $keys = [];

        foreach ($this->hashKeysToKvps as $kvp) {
            $keys[] = $kvp->key;
        }

        return $keys;
    }

    /**
     * @inheritdoc
     */
    public function getValues(): array
    {
        $values = [];

        foreach ($this->hashKeysToKvps as $kvp) {
            $values[] = $kvp->value;
        }

        return $values;
    }

    /**
     * @inheritdoc
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->containsKey($offset);
    }

    /**
     * @inheritdoc
     * @throws OutOfBoundsException Thrown if the key could not be found
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * @inheritdoc
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        /** @psalm-suppress PossiblyNullArgument This is a bug - null is just fine */
        $this->add($offset, $value);
    }

    /**
     * @inheritdoc
     * @throws RuntimeException Thrown if the value's key could not be calculated
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->removeKey($offset);
    }

    /**
     * @inheritdoc
     */
    public function removeKey(mixed $key): void
    {
        unset($this->hashKeysToKvps[$this->getHashKey($key)]);
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return \array_values($this->hashKeysToKvps);
    }

    /**
     * @inheritdoc
     */
    public function tryGet(mixed $key, mixed &$value): bool
    {
        try {
            $value = $this->get($key);

            return true;
        } catch (OutOfBoundsException) {
            return false;
        }
    }

    /**
     * Gets the hash key for a value
     * This method allows extending classes to customize how hash keys are calculated
     *
     * @param mixed $value The value whose hash key we want
     * @return string The hash key
     * @throws RuntimeException Thrown if the hash key could not be calculated
     */
    protected function getHashKey(mixed $value): string
    {
        return $this->keyHasher->getHashKey($value);
    }
}
