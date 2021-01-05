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
use Traversable;

/**
 * Defines an array list
 */
class ArrayList implements IList
{
    /** @var mixed[] The list of values */
    protected array $values = [];

    /**
     * @param mixed[] $values The list of values
     */
    public function __construct(array $values = [])
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
        /** @psalm-suppress MixedAssignment Psalm is not pulling array types from inheritdoc (#4504) - bug */
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
        \array_splice($this->values, $index, 0, $value);
    }

    /**
     * @inheritdoc
     */
    public function intersect(array $values): void
    {
        $intersectedValues = \array_intersect($this->values, $values);
        $this->clear();
        $this->addRange($intersectedValues);
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
     * @psalm-suppress MixedReturnStatement This method is correctly returning a mixed type - bug
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
        $this->insert($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->removeIndex($offset);
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
    public function reverse(): void
    {
        $this->values = \array_reverse($this->values);
    }

    /**
     * @inheritdoc
     */
    public function sort(callable $comparer): void
    {
        \usort($this->values, $comparer);
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
    public function union(array $values): void
    {
        $unionedValues = \array_merge(($this->values), $values);
        $this->clear();
        $this->addRange($unionedValues);
    }
}
