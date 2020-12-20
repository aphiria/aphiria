<?php

/**
 * Aphiria
 *
 * @link      https://www.aphiria.com
 * @copyright Copyright (C) 2020 David Young
 * @license   https://github.com/aphiria/aphiria/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Aphiria\Collections;

/**
 * Defines a key-value pair
 */
class KeyValuePair
{
    /**
     * @param mixed $key The key
     * @param mixed $value The value
     */
    public function __construct(private mixed $key, private mixed $value)
    {
    }

    /**
     * Gets the key
     *
     * @return mixed The key
     */
    public function getKey(): mixed
    {
        return $this->key;
    }

    /**
     * Gets the value
     *
     * @return mixed The value
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
