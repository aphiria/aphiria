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
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Defines a stack
 */
class Queue implements Countable, IteratorAggregate
{
    /** @var list<mixed> The values in the queue */
    protected array $values = [];

    /**
     * Clears all values from the queue
     */
    public function clear(): void
    {
        $this->values = [];
    }

    /**
     * Gets whether or not the value exists
     *
     * @param mixed $value The value to search for
     * @return bool True if the value exists, otherwise false
     */
    public function containsValue(mixed $value): bool
    {
        return \in_array($value, $this->values, false);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return \count($this->values);
    }

    /**
     * Dequeues a value from the queue
     *
     * @return mixed The value of the dequeued value if one exists, otherwise null
     */
    public function dequeue(): mixed
    {
        if (\count($this->values) === 0) {
            return null;
        }

        return \array_shift($this->values);
    }

    /**
     * Enqueues a values to the queue
     *
     * @param mixed $value The value to enqueue
     */
    public function enqueue(mixed $value): void
    {
        $this->values[] = $value;
    }

    /**
     * @inheritdoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values);
    }

    /**
     * Peeks at the value at the beginning of the queue
     *
     * @return mixed The value at the beginning of the queue if one exists, otherwise null
     */
    public function peek(): mixed
    {
        return $this->values[0] ?? null;
    }

    /**
     * Gets all of the values as an array
     *
     * @return list<mixed> All of the values
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
