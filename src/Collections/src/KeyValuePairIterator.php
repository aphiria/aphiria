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

use Iterator;

/**
 * Defines an iterator for a list of key-value pairs
 *
 * @template TKey
 * @template TValue
 * @implements Iterator<TKey, TValue>
 */
class KeyValuePairIterator implements Iterator
{
    /** @var int The current index */
    private int $currIndex = 0;

    /**
     * @param list<KeyValuePair<TKey, TValue>> $kvps The list of key-value pairs to iterate over
     */
    public function __construct(private readonly array $kvps)
    {
    }

    /**
     * @inheritdoc
     * @return TValue
     */
    public function current(): mixed
    {
        return $this->kvps[$this->currIndex]->value;
    }

    /**
     * @inheritdoc
     * @return TKey
     */
    public function key(): mixed
    {
        return $this->kvps[$this->currIndex]->key;
    }

    /**
     * @inheritdoc
     */
    public function next(): void
    {
        $this->currIndex++;
    }

    /**
     * @inheritdoc
     */
    public function rewind(): void
    {
        $this->currIndex = 0;
    }

    /**
     * @inheritdoc
     */
    public function valid(): bool
    {
        return isset($this->kvps[$this->currIndex]);
    }
}
