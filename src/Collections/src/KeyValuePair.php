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

/**
 * Defines a key-value pair
 *
 * @template TKey
 * @template TValue
 */
class KeyValuePair
{
    /**
     * @param TKey $key The key
     * @param TValue $value The value
     */
    public function __construct(private mixed $key, private mixed $value)
    {
    }

    /**
     * Gets the key
     *
     * @return TKey The key
     */
    public function getKey(): mixed
    {
        return $this->key;
    }

    /**
     * Gets the value
     *
     * @return TValue The value
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
