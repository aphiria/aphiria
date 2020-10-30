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

use ArrayIterator;
use InvalidArgumentException;
use OutOfBoundsException;
use RuntimeException;
use Traversable;

/**
 * Defines an immutable hash table
 */
class ImmutableHashTable implements IImmutableDictionary
{
    /** @var KeyValuePair[] The list of values */
    protected array $hashKeysToKvps = [];
    /** @var KeyHasher The key hasher to use */
    private KeyHasher $keyHasher;

    /**
     * @param KeyValuePair[] $kvps The list of values to add
     * @throws InvalidArgumentException Thrown if the array contains a non-key-value pair
     * @throws RuntimeException Thrown if a hash key could not be calculated
     */
    public function __construct(array $kvps)
    {
        $this->keyHasher = new KeyHasher();

        foreach ($kvps as $kvp) {
            /** @psalm-suppress DocblockTypeContradiction We want to check the types at runtime */
            if (!$kvp instanceof KeyValuePair) {
                throw new InvalidArgumentException('Value must be instance of ' . KeyValuePair::class);
            }

            $this->hashKeysToKvps[$this->getHashKey($kvp->getKey())] = $kvp;
        }
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
            if ($kvp->getValue() == $value) {
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
     */
    public function get(mixed $key): mixed
    {
        $hashKey = $this->getHashKey($key);

        if (!$this->containsKey($key)) {
            throw new OutOfBoundsException("Hash key \"$hashKey\" not found");
        }

        return $this->hashKeysToKvps[$hashKey]->getValue();
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_values($this->hashKeysToKvps));
    }

    /**
     * @inheritdoc
     */
    public function getKeys(): array
    {
        $keys = [];

        foreach ($this->hashKeysToKvps as $kvp) {
            $keys[] = $kvp->getKey();
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
            $values[] = $kvp->getValue();
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
     * @throws RuntimeException Thrown because this is immutable
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Cannot set values in ' . self::class);
    }

    /**
     * @inheritdoc
     * @throws RuntimeException Thrown because this is immutable
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new RuntimeException('Cannot unset values in ' . self::class);
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return array_values($this->hashKeysToKvps);
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
