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

use ArrayIterator;
use RuntimeException;
use Traversable;

/**
 * Defines an immutable hash set
 *
 * @template T
 * @implements IImmutableSet<T>
 */
class ImmutableHashSet implements IImmutableSet
{
    /** @var array<string, T> The set of values */
    protected array $values = [];
    /** @var KeyHasher The key hasher to use */
    private readonly KeyHasher $keyHasher;

    /**
     * @param list<T> $values The set of values
     * @throws RuntimeException Thrown if the values' keys could not be calculated
     */
    final public function __construct(array $values)
    {
        $this->keyHasher = new KeyHasher();

        foreach ($values as $value) {
            $this->values[$this->getHashKey($value)] = $value;
        }
    }

    /**
     * @inheritdoc
     */
    public function containsValue(mixed $value): bool
    {
        return isset($this->values[$this->getHashKey($value)]);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return \count($this->values);
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator(\array_values($this->values));
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return \array_values($this->values);
    }

    /**
     * Gets the hash key for a value
     * This method allows extending classes to customize how hash keys are calculated
     *
     * @param T $value The value whose hash key we want
     * @return string The hash key
     * @throws RuntimeException Thrown if the hash key could not be calculated
     */
    protected function getHashKey(mixed $value): string
    {
        return $this->keyHasher->getHashKey($value);
    }
}
