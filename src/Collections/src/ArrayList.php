<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2022 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections;

use ArrayIterator;
use Closure;
use OutOfRangeException;
use Traversable;

/**
 * Defines an array list
 *
 * @template T
 * @implements IList<T>
 * @psalm-consistent-templates Needed for safely creating new instances of this class
 */
class ArrayList implements IList
{
    /** @var list<T> The list of values */
    protected array $values = [];

    /**
     * @param list<T> $values The list of values
     */
    final public function __construct(array $values = [])
    {
        $this->addRange($values);
    }

    /**
     * @inheritdoc
     */
    public function add(mixed $value): void
    {
        $this->values[] = $value;
    }

    /**
     * @inheritdoc
     */
    public function addRange(array $values): void
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    /**
     * @inheritdoc
     */
    public function clear(): void
    {
        $this->values = [];
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
    public function insert(int $index, mixed $value): void
    {
        \array_splice($this->values, $index, 0, [$value]);
    }

    /**
     * @inheritdoc
     */
    public function intersect(array $values): static
    {
        return new static(\array_values(\array_intersect($this->values, $values)));
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
        return $this->get((int)$offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->insert((int)$offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->removeIndex((int)$offset);
    }

    /**
     * @inheritdoc
     */
    public function removeIndex(int $index): void
    {
        unset($this->values[$index]);
    }

    /**
     * @inheritdoc
     */
    public function removeValue(mixed $value): void
    {
        $index = $this->indexOf($value);

        if ($index !== null) {
            $this->removeIndex($index);
        }
    }

    /**
     * @inheritdoc
     */
    public function reverse(): static
    {
        return new static(\array_reverse($this->values));
    }

    /**
     * @inheritdoc
     */
    public function sort(Closure $comparer): static
    {
        // Get a copy of the values
        $values = $this->values;
        \usort($values, $comparer);

        return new static($values);
    }

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        return $this->values;
    }

    /**
     * @inheritdoc
     */
    public function union(array $values): static
    {
        $unionedValues = [...$this->values, ...$values];

        return new static($unionedValues);
    }
}
