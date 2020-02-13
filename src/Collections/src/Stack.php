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
class Stack implements Countable, IteratorAggregate
{
    /** @var array The values of the stack */
    protected array $values = [];

    /**
     * Clears all values from the stack
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
     * @inheritdoc
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values);
    }

    /**
     * Peeks at the value at the top of the stack
     *
     * @return mixed The value at the top of the stack if one exists, otherwise null
     */
    public function peek()
    {
        return $this->values[0] ?? null;
    }

    /**
     * Pops a value off of the stack
     *
     * @return mixed The popped value if one exists, otherwise null
     */
    public function pop()
    {
        if (count($this->values) === 0) {
            return null;
        }

        return array_shift($this->values);
    }

    /**
     * Pushes a value onto the stack
     *
     * @param mixed $value The value to push
     */
    public function push($value): void
    {
        array_unshift($this->values, $value);
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
