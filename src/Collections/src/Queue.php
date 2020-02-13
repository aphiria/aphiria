<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/master/LICENSE.md
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
    /** @var array The values in the queue */
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
    public function containsValue($value): bool
    {
        return in_array($value, $this->values);
    }

    /**
     * @inheritdoc
     */
    public function count(): int
    {
        return count($this->values);
    }

    /**
     * Dequeues a value from the queue
     *
     * @return mixed The value of the dequeued value if one exists, otherwise null
     */
    public function dequeue()
    {
        if (count($this->values) === 0) {
            return null;
        }

        return array_shift($this->values);
    }

    /**
     * Enqueues a values to the queue
     *
     * @param mixed $value The value to enqueue
     */
    public function enqueue($value): void
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
    public function peek()
    {
        return $this->values[0] ?? null;
    }

    /**
     * Gets all of the values as an array
     *
     * @return array All of the values
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
