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

use ArrayIterator;
use OutOfRangeException;
use RuntimeException;
use Traversable;

/**
 * Defines an immutable array list
 *
 * @template T
 * @implements IImmutableList<T>
 */
class ImmutableArrayList implements IImmutableList
{
    /**
     * @param list<T> $values The list of values
     */
    final public function __construct(protected readonly array $values)
    {
    }

    /**
     * @inheritdoc
     */
    public function containsValue(mixed $value): bool
    {
        return $this->indexOf($value) !== null;
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
    public function get(int $index): mixed
    {
        if ($index < 0 || $index >= \count($this)) {
            throw new OutOfRangeException("Index $index is out of range");
        }

        return $this->values[$index];
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values);
    }

    /**
     * @inheritdoc
     */
    public function indexOf(mixed $value): ?int
    {
        if (($index = \array_search($value, $this->values, false)) === false) {
            return null;
        }

        return (int)$index;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists(mixed $offset): bool
    {
        return \array_key_exists($offset, $this->values);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new RuntimeException('Cannot set values in ' . self::class);
    }

    /**
     * @inheritdoc
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
        return $this->values;
    }
}
